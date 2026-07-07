<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Customer::where('company_id', $company->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('contents.property.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('contents.property.customers.create');
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'type' => 'required|in:individual,business',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['company_id'] = $company->id;

        Customer::create($validated);

        return redirect('/customers')->with('success', 'Customer created successfully.');
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        return view('contents.property.customers.edit', compact('customer'));
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'type' => 'required|in:individual,business',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($validated);

        return redirect('/customers')->with('success', 'Customer updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');
        $customer = Customer::where('company_id', $company->id)
            ->where('uuid', $uuid)->firstOrFail();

        $customer->delete();

        return redirect('/customers')->with('success', 'Customer deleted successfully.');
    }
}
