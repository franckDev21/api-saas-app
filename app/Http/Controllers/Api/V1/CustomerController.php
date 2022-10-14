<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return CustomerResource::collection(Customer::with('company')
            ->where('company_id',$request->user()->company_id)
            ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'firstname' => 'required|min:2',
            'lastname' => 'required|min:2',
            'tel' => 'string:max:100',
            'address' => 'string|max:100',
            'email' => 'email'
        ]);

        Customer::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname ,
            'tel' => $request->tel ?? null,
            'address' => $request->address ?? null,
            'email' => $request->email ?? null,
            'company_id' => $request->user()->company_id,
        ]);

        return response([
            'message' => "Your customer has been successfully created !"
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        //
    }
}
