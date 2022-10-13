<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CompanyResource::collection(Company::all());
    }

    public function myCompany(Request $request){
        return $request->user()->company;
    }

    public function updatePictureCompany(Request $request,Company $company){
        // we check if it is the user's company

        // we update the company's logo
        $request->validate([
            'image' => 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF|max:4000'
        ]);

        $filename = time() . '.' . $request->image->extension();
        $path = $request->image->storeAs('img/companies', $filename, 'public');

        $company->update([
            'logo' => $path
        ]);

        return response([
            'message' => 'The lego of your company has been successfully updated',
            'path'    => $path
        ],201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        // we check if it is the user's company
        $rules = [
            'name' => 'required',
            'address' => 'required',
            'country' => 'required',
            'city' => 'required',
            'email' => 'required|email|max:50|unique:companies',
            'number_of_employees' => 'required',
        ];

        if($request->description){
            $rules['description'] = 'max:255';
        }

        if($request->tel){
            $rules['tel'] = 'string';
        }

        if($request->postal_code){
            $rules['postal_code'] = 'max:200';
        }

        $data = $request->validate($rules);

        $company = Company::create($data);

        $request->user()->update([
            'as_company' => true,
            'company_id' => $company->id
        ]);

        return response([
            'message' => 'Your company has been successfully created ',
            'company_id' => $company->id
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        return CompanyResource::make($company);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        // we check if it is the user's company
        $rules = [
            'name' => 'required',
            'address' => 'required',
            'country' => 'required',
            'city' => 'required',
            'tel' => 'required',
            'email' => 'required|email|max:50',
            'number_of_employees' => 'required',
        ];

        if($request->description){
            $rules['description'] = 'max:255';
        }

        if($request->postal_code){
            $rules['postal_code'] = 'max:200';
        }

        $data = $request->validate($rules);

        $company->update($data);

        return response([
            'message' => 'The information of your company has been successfully updated'
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        // 
        
    }
}
