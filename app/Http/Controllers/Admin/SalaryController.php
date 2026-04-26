<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PaymentMethod;
use App\Models\Salary;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $expenseManagement = 'active';
        $salaryActiveClass = 'active';
        $pageTitle = 'Employee Salaries';

        $query = Salary::with('employee', 'payments');

        if ($request->employee) {
            $query->where('employee_id', $request->employee);
        }

        if ($request->salary_for) {
            $monthName = $request->salary_for;
            $year = date('Y');
            // Convert month name to a Carbon date (first day of the month)
            $salary_for = Carbon::createFromFormat('F Y', $monthName.' '.$year);
            $query->whereMonth('salary_for', $salary_for->month)
                ->whereYear('salary_for', $salary_for->year);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $employee = Employee::select('id', 'employee_name')->get();

        $salaries = $query->latest()->paginate();

        if ($request->ajax()) {
            return view('backend.salary.table', compact('salaries'));
        }

        return view('backend.salary.index', compact('salaries', 'employee', 'expenseManagement', 'salaryActiveClass', 'pageTitle'));
    }

    public function create()
    {
        $data['expenseManagement'] = 'active';
        $data['pageTitle'] = 'Employee Salary Generate';
        $data['salaryActiveClass'] = 'active';

        $data['employees'] = Employee::select('id', 'employee_name', 'basic_salary')->get();

        return view('backend.salary.create')->with($data);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|min:0',
            // 'others' => 'nullable|min:0',
            'over_time' => 'nullable|min:0',
            'bonus' => 'nullable|min:0',
            'deductions' => 'nullable|min:0',
            'total_salary' => 'required|min:0',
            'salary_for' => 'required',
            'note' => 'nullable|string|max:255',
        ]);

        // Check if the employee has already received a salary in the same month
        $employeeId = $request->employee_id;
        $monthName = $request->salary_for;
        $year = date('Y'); // Get the current year

        // Convert month name to a Carbon date (first day of the month)
        $salary_for = Carbon::createFromFormat('F Y', $monthName.' '.$year);


        $existingSalary = Salary::with('employee:id,employee_name')
            ->where('employee_id', $employeeId)
            ->whereMonth('salary_for', $salary_for->month)
            ->whereYear('salary_for', $salary_for->year)
            ->first();

        if ($existingSalary) {
            $employeeName = "{$existingSalary->employee->employee_name}";

            return back()->with('errorSalary', "The employee {$employeeName} has already generated a salary for this month {$request->salary_for} {$year}.");
        }

        // Create the salary record
        $salary = Salary::create([
            'employee_id' => $request->employee_id,
            'basic_salary' => $request->basic_salary,
            // 'others' => $request->others ?? 0,
            'over_time' => $request->over_time ?? 0,
            'bonus' => $request->bonus ?? 0,
            'deductions' => $request->deductions ?? 0,
            'advance'   =>$request->advance ?? 0,
            'total_salary' => $request->total_salary,
            'salary_for' => $salary_for,
            'status' => 'due',
            'note' => $request->note,
        ]);

        $salary->transactions()->create([
            'payment_method_id' => null, // No payment method here since it's not paid yet
            'amount' => $salary->total_salary,
            'debit' => 'debit',  // Debit the salary expense
            'credit' => null,                  // No credit yet
            'transaction_type' => 'salary_generated',
            'transaction_date' => now(),
            'note' => 'Salary generated for: '.optional($salary->employee)->employee_name,
        ]);

        return redirect()->route('admin.salary.index')->with('success', 'Salary generated successfully.');
    }

    public function edit($id)
    {
        $data['expenseManagement'] = 'active';
        $data['salaryActiveClass'] = 'active';

        $data['salary'] = Salary::with('employee:id,employee_name')->findOrFail($id);

        $data['pageTitle'] = $data['salary']->employee->employee_name.' Salary Details';

        $data['employees'] = Employee::select('id', 'employee_name', 'basic_salary')->get();

        return view('backend.salary.edit')->with($data);
    }

    public function update(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'basic_salary' => 'required|min:0',
            // 'others' => 'nullable|min:0',
            'over_time' => 'nullable|min:0',
            'bonus' => 'nullable|min:0',
            'deductions' => 'nullable|min:0',
            'total_salary' => 'required|min:0',
            'salary_for' => 'required',
            'note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Get the salary record
            $salary = Salary::findOrFail($id);
            $employeeId = $request->employee_id;
            $monthName = $request->salary_for;
            $year = date('Y');

            $salary_for = Carbon::createFromFormat('F Y', $monthName.' '.$year);

            // Check if already generated salary in same month (excluding current one)
            $existingSalary = Salary::with('employee:id,employee_name')
                ->where('employee_id', $employeeId)
                ->whereMonth('created_at', $salary_for->month)
                ->whereYear('created_at', $salary_for->year)
                ->where('id', '!=', $id)
                ->first();

            if ($existingSalary) {
                $employeeName = "{$existingSalary->employee->employee_name}";

                return back()->with('errorSalary', "The employee {$employeeName} has already generated a salary for this month {$request->salary_for} {$year}.");
            }

            // Update the salary record
            $salary->update([
                'employee_id' => $request->employee_id,
                'basic_salary' => $request->basic_salary,
                // 'others' => $request->others ?? 0,
                'over_time' => $request->over_time ?? 0,
                'bonus' => $request->bonus ?? 0,
                'deductions' => $request->deductions ?? 0,
                'advance' => $request->advance ?? 0,
                'total_salary' => $request->total_salary,
                'salary_for' => $salary_for,
                'note' => $request->note,
            ]);

            // Update the corresponding salary transaction (debit)
            $transaction = $salary->transactions()
                ->where('transaction_type', 'salary_expense')
                ->whereNull('credit') // only generation entry
                ->first();

            if ($transaction) {
                $transaction->update([
                    'amount' => $request->total_salary,
                    'debit' => $request->total_salary,
                    'note' => 'UPDATED: Salary generated for '.optional($salary->employee)->employee_name,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.salary.index')->with('success', 'Salary updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->with('errorSalary', 'Failed to update salary.');
        }
    }

    public function salaryPayment($id, Request $request)
    {
        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|exists:payment_methods,id',
        ]);

        DB::beginTransaction();

        try {
            $salary = Salary::with(['employee', 'payments'])->findOrFail($id);
            $paymentAccount = PaymentMethod::findOrFail($request->payment_method);
            $amount = $request->payment_amount;

            // Check if account has sufficient balance
            if ($paymentAccount->current_balance < $amount) {
                return back()->with('error', 'Insufficient balance in selected payment account.');
            }

            // Create salary payment
            $salaryPayment = SalaryPayment::create([
                'salary_id' => $salary->id,
                'employee_id' => $salary->employee_id,
                'amount_paid' => $amount,
                'payment_method' => $paymentAccount->id,
                'note' => $request->note,
            ]);

            // Update salary status
            $totalPaid = $salary->payments->sum('amount_paid') + $amount;
            $salary->status = match (true) {
                $totalPaid >= $salary->total_salary => 'paid',
                $totalPaid > 0 => 'partial',
                default => 'due',
            };

            // Create transaction record under the Salary
            $salary->transactions()->create([
                'amount' => $amount,
                'debit' => null,   // No debit for the payment itself
                'credit' => 'credit',  // Credit the payment method (cash or bank)
                'transaction_type' => 'salary_paid',
                'transaction_date' => now(),
                'note' => 'SALARY PAID: '.optional($salary->employee)->employee_name,
                'payment_method_id' => $paymentAccount->id,
            ]);

            // Decrement balances
            $paymentAccount->decrement('current_balance', $amount);

            $salary->save();

            DB::commit();

            return redirect()->back()->with('success', 'Salary payment recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()->with('error', 'Something went wrong while recording the salary payment.');
        }
    }

    public function show($id)
    {
        $data['expenseManagement'] = 'active';
        $data['salaryActiveClass'] = 'active';

        $data['salary'] = Salary::with('employee:id,employee_name', 'payments.paymentMethod')->findOrFail($id);

        $data['pageTitle'] = $data['salary']->employee->employee_name.' Salary Details';

        return view('backend.salary.show')->with($data);
    }

    public function destroy($id)
    {
        $salary = Salary::findOrFail($id);

        $salary->delete();

        return back()->with('success', 'Employee salary deleted successfully.');
    }
}
