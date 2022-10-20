<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSupplier;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::with(['productSupplier','category','ProductType'])->latest()->get();
    }

    public function getTypes(){
        return ProductType::all();
    }

    public function getSuppliers(){
        return ProductSupplier::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'name' => 'required|max:100|min:1',
            'prix_unitaire' => 'required',
            'type_approvisionnement' => 'required',
            'qte_stock_alert' => 'required',
            'category_id' => 'required',
            'product_supplier_id' => 'required',
            'product_type_id' => 'required',
        ];

        if($request->poids){
            $rules['poids'] = 'required';
        }

        if($request->description){
            $rules['description'] = 'sometimes|string|min:1|max:255';
        }

        if($request->nbre_par_carton){
            $rules['nbre_par_carton'] = 'required';
        }

        if($request->image){
            $rules['image'] = 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF|max:4000';
    
            $filename = time() . '.' . $request->image->extension();
            $path = $request->image->storeAs('img/products', $filename, 'public');
        }

        if($request->qte_en_litre){
            $rules['qte_en_litre'] = 'required';
        }

        $data = $request->validate($rules);

        $data['image'] = $path ?? null;

        Product::create($data);

        return response([
            'message' => "Your product has been successfully registered"
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response([
            'message' => "Your product has been successfully deleted"
        ],201);
    }
}
