<?php

namespace App\Http\Controllers\Admin;

use App\Constants\CommonConstant;
use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\SteadfastCourierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    protected $courierService;

    public function __construct(SteadfastCourierService $courierService)
    {
        $this->courierService = $courierService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['pageTitle'] = 'Account List';
        $data['accountsActiveClass'] = 'active';
        $data['payment_method_active'] = 'active';
        $data['paymentMethods'] = PaymentMethod::all();

        // Check if any payment method is of type STEADFAST
        $hasSteadfast = $data['paymentMethods']->contains(function ($method) {
            return $method->type === CommonConstant::STEADFAST;
        });

        // Default value for Steadfast balance
        $data['steadfast_current_balance'] = null;

        if ($hasSteadfast) {
            try {
                $response = $this->courierService->getSteadfastCurrentBalance();
                if (is_array($response) && isset($response['current_balance'])) {
                    $data['steadfast_current_balance'] = $response['current_balance'];
                }
            } catch (\Throwable $e) {
                // Optionally log the error
                Log::error('Steadfast API error: ' . $e->getMessage());
                $data['steadfast_current_balance'] = 'Unavailable';
            }
        }

        return view('backend.payment_method.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['pageTitle'] = 'Account Create';
        $data['payment_method_active'] = 'active';
        $data['accountsActiveClass'] = 'active';

        return view('backend.payment_method.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
        ]);

        try {
            DB::transaction(function () use ($request) {
                if ($request->type === CommonConstant::STEADFAST) {
                    $exists = PaymentMethod::where('type', CommonConstant::STEADFAST)->exists();
                    if ($exists) {
                        throw new \Exception('A Steadfast account already exists.');
                    }
                }

                $paymentMethod = PaymentMethod::create([
                    'name' => $request->name,
                    'type' => $request->type,
                    'account_number' => $request->account_number,
                    'branch_name' => $request->branch_name,
                    'account_name' => $request->account_name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                ]);

                $settings = GeneralSetting::first();
                $settings->update([
                    'opening_balance' => $settings->opening_balance + $paymentMethod->current_balance,
                ]);
            });

            return redirect()->route('admin.accounts.index')->with('success', 'Account Created Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $data = [
            'pageTitle' => 'Account Details',
            'payment_method_active' => 'active',
            'accountsActiveClass' => 'active',
            'paymentMethod' => $paymentMethod,
            'steadfast_current_balance' => null,
        ];

        // Only fetch Steadfast balance if this is a Steadfast account
        if ($paymentMethod->type === CommonConstant::STEADFAST) {
            try {
                $response = $this->courierService->getSteadfastCurrentBalance();
                if (is_array($response) && isset($response['current_balance'])) {
                    $data['steadfast_current_balance'] = $response['current_balance'];
                }
            } catch (\Throwable $e) {
                Log::error('Steadfast API error: ' . $e->getMessage());
                $data['steadfast_current_balance'] = 'Unavailable';
            }
        }

        return view('backend.payment_method.show', $data);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required',
        ]);

        $paymentMethod = PaymentMethod::findOrFail($id);

        // Prevent duplicate Steadfast type
        if ($request->type === CommonConstant::STEADFAST) {
            $exists = PaymentMethod::where('type', CommonConstant::STEADFAST)
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors([
                    'type' => 'A Steadfast account already exists.'
                ])->withInput();
            }
        }

        // Update payment method fields
        $paymentMethod->update([
            'type' => $request->type,
            'name' => $request->name,
            'account_number' => $request->account_number,
            'branch_name' => $request->branch_name,
            'account_name' => $request->account_name,
            'current_balance' => $request->current_balance,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Account Updated Successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        PaymentMethod::find($id)->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Account Deleted Successfully');
    }

    public function getSteadfastCurrentBalance()
    {
        $response = $this->courierService->getSteadfastCurrentBalance();

        return response()->json($response);
    }
    

    public function updateOwnerCapital(Request $request)
    {
        // Validate the input
        $request->validate([
            'opening_balance' => 'required|numeric',
        ]);

        // Update the opening balance in the database
        $general = GeneralSetting::first();
        $general->opening_balance = $request->input('opening_balance');
        $general->save();

        Cache::forget('general_setting');

        // Return the updated balance to the front end
        return response()->json(['updated_balance' => $general->opening_balance]);
    }

    public function transferFunds(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required',
            'to_account_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
        ]);

        try {
            $amount = $request->amount;
            $transactionDate = $request->transaction_date ?? now();
            $note = $request->note ?? '';
            $adminName = auth()->guard('admin')->user()->name;

            $toAccount = PaymentMethod::findOrFail($request->to_account_id);

            // Handle transfer from SteadFast virtual account
            if ($request->from_account_id === 'steadfast') {
                $transaction = Transaction::create([
                    'from_account_id' => null,
                    'steadfast_account' => 'steadfast',
                    'to_account_id' => $toAccount->id,
                    'account_balance_was' => $request->balance_was,
                    'amount' => $amount,
                    'transaction_type' => 'transfer',
                    'transaction_date' => $transactionDate,
                    'note' => "{$note} Transferred to account {$toAccount->name} (AC: {$toAccount->account_number}) by {$adminName}",
                ]);

                $toAccount->increment('current_balance', $amount);

                // Log the activity
                activity()
                    ->performedOn($transaction)
                    ->event('transfer_fund')
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'amount' => $amount,
                        'from' => 'Steadfast Account',
                        'to' => $toAccount->name,
                        'type' => 'Fund Transfer',
                    ])
                    ->log('Fund transferred from Steadfast account');

                return redirect()->back()->with('success', 'Funds transferred successfully.');
            }

            // Handle transfer between actual accounts
            $fromAccount = PaymentMethod::findOrFail($request->from_account_id);

            if ($fromAccount->current_balance < $amount) {
                return redirect()->back()->with('error', 'Insufficient balance in the source account.');
            }

            DB::beginTransaction();

            // Update balances
            $fromAccount->decrement('current_balance', $amount);
            $toAccount->increment('current_balance', $amount);

            // Create transaction record
            $transaction = Transaction::create([
                'from_account_id' => $fromAccount->id,
                'steadfast_account' => null,
                'to_account_id' => $toAccount->id,
                'account_balance_was' => $request->balance_was,
                'amount' => $amount,
                'transaction_type' => 'transfer',
                'transaction_date' => $transactionDate,
                'note' => "{$note} Transferred to account {$toAccount->name} (AC: {$toAccount->account_number}) by {$adminName}",
            ]);

            // Log the activity
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'amount' => $amount,
                    'from' => $fromAccount->name,
                    'to' => $toAccount->name,
                    'type' => 'Fund Transfer',
                ])
                ->log('Fund transferred between accounts');

            DB::commit();

            return redirect()->back()->with('success', 'Funds transferred successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred while transferring funds: ' . $e->getMessage());
        }
    }

    public function addFund(Request $request)
    {
        $request->validate([
            'account_to_add' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Find the account to which funds will be added
            $toAccount = PaymentMethod::find($request->account_to_add);
            // Add the specified amount to the account's current balance
            $toAccount->current_balance += $request->amount;
            $toAccount->save();

            // Create transaction records for both the debit and credit
            $transaction = $toAccount->transactions()->create([
                'from_account_id' => null, // Ensure this field is nullable in the database
                'to_account_id' => $toAccount->id,
                'account_balance_was' => $toAccount->current_balance - $request->amount,
                'amount' => $request->amount,
                'debit' => 'debit',
                'transaction_type' => 'add_fund',
                'transaction_date' => $request->transaction_date ?? now(),
                'note' => $request->note . ' Funds added to account ' . $toAccount->name . ' ( ' . $toAccount->account_number . ' ) ' . ' by ' . auth()->guard('admin')->user()->name,
            ]);

            $settings = GeneralSetting::first();
            $settings->update([
                'opening_balance' => $settings->opening_balance + $request->amount,
            ]);

            // Log the activity
            activity()
                ->performedOn($transaction)
                ->event('added_fund')
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'amount' => $request->amount,
                    'account' => $toAccount->name,
                    'type' => 'Add Fund',
                ])
                ->log('Fund added to account');

            // Commit the transaction
            DB::commit();

            // Set the flag for the active tab
            return redirect()->back()->with(['success' => 'Funds added successfully.', 'activeTab' => 'add_fund']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred while added funds: ' . $e->getMessage());
        }
    }

    public function withdrawFund(Request $request)
    {
        $request->validate([
            'account_to_add' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            $toAccount = PaymentMethod::findOrFail($request->account_to_add);

            // Ensure current_balance is numeric
            if (! is_numeric($toAccount->current_balance)) {
                $toAccount->current_balance = 0;
            }

            // Check if the account has sufficient balance
            if ($toAccount->current_balance < $request->amount) {
                return redirect()->back()->with('error', 'Insufficient balance in the selected account.');
            }

            $oldBalance = $toAccount->current_balance;

            // Decrement the balance
            $toAccount->decrement('current_balance', $request->amount);

            // Log the transaction
            $transaction = $toAccount->transactions()->create([
                'from_account_id' => null, // nullable
                'to_account_id' => $toAccount->id,
                'account_balance_was' => $oldBalance,
                'amount' => $request->amount,
                'credit' => 'credit',
                'transaction_type' => 'withdraw_fund',
                'transaction_date' => $request->transaction_date ?? now(),
                'note' => ($request->note ?? '') . ' Funds withdrawn from account ' . $toAccount->name . ' (' . $toAccount->account_number . ') by ' . auth()->guard('admin')->user()->name,
            ]);

            // Update general settings if needed
            $settings = GeneralSetting::first();
            if ($settings) {
                $settings->update([
                    'opening_balance' => max(0, $settings->opening_balance - $request->amount),
                ]);
            }

            // Log the activity
            activity()
                ->performedOn($transaction)
                ->event('withdraw_fund')
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'amount' => $request->amount,
                    'account' => $toAccount->name,
                    'type' => 'Withdraw Fund',
                ])
                ->log('Fund withdrawn from account');

            DB::commit();

            return redirect()->back()->with([
                'success' => 'Funds withdrawn successfully.',
                'activeTab' => 'withdraw_fund',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'An error occurred while withdrawing funds: ' . $e->getMessage());
        }
    }

    public function fraudCheck(Request $request)
    {
        $request->validate([
            'phoneNumber' => 'required',
        ]);

        $phone = $request->phoneNumber;

        // Generate a unique cache key based on the phone number
        $cacheKey = 'fraud_check_' . md5($phone);

        // Check if the data is already cached
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $url = 'https://bdcourier.com/api/courier-check?phone=' . urlencode($phone);

        $response = Http::withToken(env('COURIER_HISTORY_API_TOKEN'))->post($url);

        if ($response->successful()) {
            $data = $response->json();

            // Cache the response for one day
            Cache::put($cacheKey, $data, now()->addMinutes(10));

            return response()->json($data);
        } else {
            return response()->json(['error' => 'Failed to fetch data from the API'], $response->status());
        }
    }
}
