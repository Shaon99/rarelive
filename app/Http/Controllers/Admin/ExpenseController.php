<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $expenseManagement = 'active';
        $pageTitle = 'Expense';
        $expenseActive = 'active';

        $query = Expense::with('expenseCategory', 'paymentAccount:id,name,account_number');

        if ($request->filled('category')) {
            $query->where('expense_category_id', $request->category);
        }

        $expenses = $query->latest()->paginate();

        if ($request->ajax()) {
            return view('backend.expense.expense-table', compact('expenses'))->render();
        }

        $categories = ExpenseCategory::all();

        return view('backend.expense.index', compact('categories', 'expenseManagement', 'expenses', 'expenseActive', 'pageTitle'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Expense Create';
        $data['expenseManagement'] = 'active';
        $data['expense'] = 'active';
        $data['expenseCategories'] = ExpenseCategory::all();
        $data['payment_account'] = PaymentMethod::select('id', 'type', 'name', 'account_number')
            ->where('type', '!=', 'STEADFAST')
            ->get()
            ->values();

        return view('backend.expense.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'expenseCategories' => 'required|array',
            'expenseCategories.*' => 'required|exists:expense_categories,id',
            'amounts' => 'required|array',
            'amounts.*' => 'required|numeric|min:0.01',
            'payment_account' => 'required|exists:payment_methods,id',
            'date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = array_sum($request->amounts);
            $paymentAccount = PaymentMethod::findOrFail($request->payment_account);

            // Check total balance
            if ($paymentAccount->current_balance < $totalAmount) {
                return back()->with('error', 'Insufficient account balance.');
            }

            foreach ($request->expenseCategories as $index => $categoryId) {
                $amount = $request->amounts[$index];

                $expense = Expense::create([
                    'invoice_no' => generateUniqueSerial('expenses', 'invoice_no', 'EX.'),
                    'expense_category_id' => $categoryId,
                    'date' => $request->date,
                    'amount' => $amount,
                    'payment_method_id' => $paymentAccount->id,
                    'note' => $request->note,
                ]);

                // Decrement from account
                $paymentAccount->decrement('current_balance', $amount);

                // Ledger
                $expense->transactions()->create([
                    'payment_method_id' => $paymentAccount->id,
                    'amount' => $amount,
                    'debit' => 'debit', // Debit for the expense
                    'credit' => null,   // No credit (if expense is directly paid)
                    'transaction_type' => 'expense_on_payment',
                    'transaction_date' => now(),
                    'note' => 'Expense: '.optional($expense->expenseCategory)->name.' | '.$request->note,
                ]);

                activity()
                    ->performedOn($expense)
                    ->causedBy(auth()->guard('admin')->user())
                    ->event('expense_created')
                    ->withProperties([
                        'reference_id' => $expense->id,
                        'reference_type' => Expense::class,
                    ])
                    ->log('Created Expense of '.$amount);
            }

            DB::commit();

            return redirect()->route('admin.expense.index')
                ->with('success', 'Multiple expenses recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to store expenses: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['pageTitle'] = 'Expense Edit';
        $data['expenseManagement'] = 'active';
        $data['expense'] = 'active';
        $data['singleExpense'] = Expense::findOrFail($id);
        $data['expensecategories'] = ExpenseCategory::all();

        return view('backend.expense.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'expensecategories' => 'required',
            'amount' => 'required',
            'date' => 'required',
        ]);
        $expense = Expense::findOrFail($id);

        $expense->expense_category_id = $request->expensecategories;
        $expense->date = $request->date;
        $expense->amount = $request->amount;
        $expense->note = $request->note;

        $expense->save();

        return redirect()
            ->route('admin.expense.index')
            ->with('success', 'expense update successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Expense::find($id)->delete();

        return back()->with('success', 'Expense Deleted successfully');
    }

    public function expenseRestore($id)
    {
        Expense::withTrashed()->find($id)->restore();

        return back()->with('success', 'Expense Successfully Restore');
    }

    public function expenseDelete($id)
    {
        Expense::onlyTrashed()->findOrFail($id)->forceDelete();

        return back()->with('success', 'Expense Deleted successfully');
    }

    public function expenseLedger(Request $request)
    {
        $pageTitle = 'Expenses Ledger';
        $expensesLedgerActiveClass = 'active';
        $accountsActiveClass = 'active';

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $openingBalance = 0;
        $closingBalance = 0;
        $totals = (object) [
            'total_debit' => 0,
            'total_credit' => 0,
        ];
        $transactions = collect();

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay()->format('Y-m-d H:i:s');
            $end = Carbon::parse($endDate)->endOfDay()->format('Y-m-d H:i:s');
            $types = [Expense::class, Salary::class];

            // 1. Opening Balance
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $openingBalanceResult = DB::selectOne("
            SELECT 
                IFNULL(SUM(CASE WHEN debit IS NOT NULL THEN amount ELSE 0 END), 0) - 
                IFNULL(SUM(CASE WHEN credit IS NOT NULL THEN amount ELSE 0 END), 0) AS balance
            FROM transactions
            WHERE transactionable_type IN ($placeholders) AND created_at < ?
        ", array_merge($types, [$start]));

            $openingBalance = $openingBalanceResult->balance ?? 0;

            // 2. Transactions with extra fields for expenses & salaries
            $transactions = collect(DB::select('
            SELECT 
                t.created_at AS date,
                t.note,
                t.amount,
                t.transaction_type,
                t.debit,
                t.credit,
                t.transactionable_id,
                t.transactionable_type,
                t.payment_method_id,
                e.invoice_no,
                s.salary_for,
                emp.employee_name,
                pm.name AS payment_methods,
                CASE WHEN t.debit IS NOT NULL THEN t.amount ELSE 0 END AS debit_amount,
                CASE WHEN t.credit IS NOT NULL THEN t.amount ELSE 0 END AS credit_amount
            FROM transactions t
            LEFT JOIN expenses e ON t.transactionable_type = ? AND t.transactionable_id = e.id
            LEFT JOIN salaries s ON t.transactionable_type = ? AND t.transactionable_id = s.id
            LEFT JOIN employees emp ON s.employee_id = emp.id
            LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
            WHERE t.transactionable_type IN (?, ?) AND t.created_at BETWEEN ? AND ?
            ORDER BY t.created_at ASC
        ', [
                Expense::class,
                Salary::class,
                ...$types,
                $start,
                $end,
            ]));

            // 3. Running Balance
            $runningBalance = $openingBalance;

            foreach ($transactions as $transaction) {
                $runningBalance += $transaction->debit_amount;
                $runningBalance -= $transaction->credit_amount;
                $transaction->balance = $runningBalance;
            }

            // 4. Totals
            $totals->total_debit = $transactions->sum('debit_amount');
            $totals->total_credit = $transactions->sum('credit_amount');
            $closingBalance = $runningBalance;
        }

        $viewData = compact(
            'startDate',
            'endDate',
            'transactions',
            'openingBalance',
            'closingBalance',
            'totals',
            'expensesLedgerActiveClass',
            'accountsActiveClass',
            'pageTitle'
        );

        if ($request->ajax()) {
            return view('backend.accounts.expenses-ledger-table', $viewData)->render();
        }

        return view('backend.accounts.expenses-ledger', $viewData);
    }
}
