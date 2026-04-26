<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddressBook;
use App\Models\Customer;
use App\Models\Sales;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['pageTitle'] = 'Customers';
        $data['customer'] = 'active';
        $data['customers'] = 'active';

        return view('backend.customer.index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['pageTitle'] = 'Customer Create';
        $data['customer'] = 'active';
        $data['customers'] = 'active';

        // Read JSON file from public/assets directory
        $json = file_get_contents(public_path('assets/address.json'));
        $city = json_decode($json, true);
        $data['districts'] = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();

        return view('backend.customer.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:customers',
            'address' => 'required',
        ]);

        if ($request->ajax()) {
            Customer::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'social_type' => $request->social_type,
                'social_id' => $request->social_id,
            ]);

            $customer = Customer::latest()->first();

            $customerAddress = AddressBook::create([
                'customer_id' => $customer->id,
                'address' => $request->address,
                'city' => $request->city,
                'thana' => $request->thana,
                'zone' => $request->zone,
            ]);

            return response()->json(['customer' => $customerAddress]);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'social_type' => $request->social_type,
            'social_id' => $request->social_id,
        ]);

        AddressBook::create([
            'customer_id' => $customer->id,
            'address' => $request->address,
            'city' => $request->city,
            'thana' => $request->thana,
            'zone' => $request->zone,
        ]);

        return redirect()->route('admin.customer.index')->with('success', 'Customer created successfully');
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
        $data['singleCustomers'] = Customer::findOrFail($id);

        $data['pageTitle'] = 'Edit Customer '.$data['singleCustomers']->name;
        $data['customer'] = 'active';
        $data['customers'] = 'active';

        // Read JSON file from public/assets directory
        $json = file_get_contents(public_path('assets/address.json'));
        $city = json_decode($json, true);
        $data['districts'] = collect($city['district'])->map(function ($district) {
            return [
                'name' => $district['name'],
                'bn_name' => $district['bn_name'] ?? null,
            ];
        })->all();

        return view('backend.customer.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:customers,phone,'.$customer->id,
        ]);

        // Update customer details
        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'social_type' => $request->social_type,
            'social_id' => $request->social_id,
        ]);

        if ($request->address) {
            // If new address is provided, create a new address entry
            AddressBook::create([
                'customer_id' => $customer->id,
                'address' => $request->address,
                'city' => $request->city,
                'thana' => $request->thana,
                'zone' => $request->zone,
            ]);
        } elseif ($request->city || $request->thana) {
            // If no new address but city/thana/zone needs updating, update the latest address
            $existingAddress = AddressBook::where('customer_id', $customer->id)->latest()->first();

            if ($existingAddress) {
                $existingAddress->update([
                    'city' => $request->city ?? $existingAddress->city,
                    'thana' => $request->thana ?? $existingAddress->thana,
                    'zone' => $request->zone ?? $existingAddress->zone,
                ]);
            }
        }

        return redirect()->route('admin.customer.index')->with('success', 'Customer updated successfully');
    }

    public function customerOrderHistory($id)
    {
        $data['customerP'] = Customer::findOrFail($id);
        $data['customerLedger'] = Sales::where('customer_id', $id)->get();
        $data['pageTitle'] = $data['customerP']->name.' Order List';
        $data['customer'] = 'active';
        $data['customer'] = 'active';
        $data['customers'] = 'active';

        return view('backend.customer.customerLedger')->with($data);
    }

    public function customerLedger($id)
    {
        $customer = Customer::findOrFail($id);

        $ledger = Transaction::with('paymentMethod')
            ->where('customer_id', $id)
            ->whereNotIn('transaction_tag', ['payment_received_1', 'steadfast_credit'])
            ->oldest('transaction_date')
            ->get();

        $data = [
            'customerP' => $customer,
            'pageTitle' => $customer->name.' Ledger',
            'customer' => 'active',
            'customers' => 'active',
            'ledger' => $ledger,
        ];

        return view('backend.customer.customer-ledger', $data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Customer::find($id)->delete();

        return back()->with('success', 'Customer Deleted successfully');
    }

    public function customerRestore($id)
    {
        Customer::withTrashed()->find($id)->restore();

        return back()->with('success', 'customer Successfully Restore');
    }

    public function customerDelete($id)
    {
        $product = Customer::onlyTrashed()->findOrFail($id);

        $product->forceDelete();

        return back()->with('success', 'customer Deleted successfully');
    }

    public function getDue($id)
    {
        $customer = Customer::select('previous_due')->find($id);

        $customerDue = Sales::select('id', 'due_amount')->where('due_amount', '>', 0)->where('customer_id', $id)->get();

        return response()->json(['customerDue' => $customerDue, 'previous_due' => $customer]);
    }

    public function searchCustomer(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        // Find customer by phone
        $customer = Customer::where('phone', $request->phone)->with('addressBooks')->first();

        if ($customer) {
            return response()->json(['status' => 'found', 'customer' => $customer]);
        } else {
            return response()->json(['status' => 'not_found']);
        }
    }

    public function suggestCustomer(Request $request)
    {
        $request->validate(['query' => 'required|string|min:2']);

        $query = trim((string) $request->input('query', ''));

        $customers = Customer::query()
            ->select('id', 'name', 'phone')
            ->where(function ($q) use ($query) {
                $q->where('phone', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%");
            })
            ->orderByRaw("CASE WHEN phone LIKE ? THEN 0 ELSE 1 END", ["{$query}%"])
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json([
            'status' => 'success',
            'customers' => $customers,
        ]);
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'address' => 'required|string|max:500',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (! $customer) {
            return response()->json(['status' => 'not_found']);
        }

        AddressBook::create([
            'customer_id' => $customer->id,
            'address' => $request->address,
            'city' => $request->city,
            'thana' => $request->thana ?? null,
            'zone' => $request->zone ?? null,
        ]);

        // Compose latest address string properly
        $latestAddress = $request->address;
        $addressCount = $customer->addressBooks()->count();

        return response()->json([
            'status' => 'success',
            'latestAddress' => $latestAddress,
            'newAddressIndex' => $addressCount,
        ]);
    }

    public function destroyAddress($id)
    {
        $address = AddressBook::findOrFail($id);
        $address->delete();

        return back()->with('success', 'Customer  address deleted successfully');
    }

    public function updateAddress(Request $request, $id)
    {
        $address = AddressBook::findOrFail($id);
        $address->update([
            'city' => $request->city,
            'thana' => $request->thana,
            'zone' => $request->zone,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.customer.edit', $address->customer_id)
            ->with('success', 'Address updated successfully.');
    }

    public function getThana(Request $request)
    {
        $cityName = $request->query('cityName');
        $json = file_get_contents(public_path('assets/address.json'));
        // Decode the JSON data into an array
        $data = json_decode($json, true);

        // Find the district by matching the city name
        $district = collect($data['district'])->firstWhere('name', $cityName);

        if ($district) {
            $thana = $district['thana'];

            return response()->json($thana);
        }

        return response()->json([]);
    }
}
