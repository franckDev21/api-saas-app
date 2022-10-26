<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductSupplier;
use App\Models\ProductType;
use App\Models\Supply;
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

    public function getAllHistory(){
        return ProductHistory::with(['product','user'])->latest()->get();
    }

    public function getProcurement(){
        return Supply::with(['product','user'])->latest()->get();
    }
    
    public function addInput(Request $request,Product $product){
        $request->validate([
            'quantite'   => 'required',
            'prix_achat'  => 'required'
        ]);

        // increment qte product
        $product->update([
            'qte_en_stock' => ((int)$product->qte_en_stock += (int)$request->quantite),
            'is_stock'     => ((int)$product->qte_en_stock += (int)$request->quantite) > 0
        ]);

        // on recupere le produit et verificie si son stock est > au stock_alert
        $product = Product::find($product->id);
        // if((int)$product->qte_en_stock > (int)$product->qte_stock_alert){
        //     foreach($product->unreadNotifications as $notification){
        //         $notification->markAsRead();
        //     }
        // }

        // new approvisionnement
        Supply::create([
            'product_id' => $product->id,
            'prix_achat' => $request->prix_achat,
            'quantite'   => $request->quantite,
            'user_id'    => $request->user()->id,
            'is_unite'   => false
        ]);

        // new hitory
        ProductHistory::create([
            'quantite'  => $request->quantite,
            'type'      => 'ENTRÉE',
            'motif'     => 'Approvisionnement',
            'product_id' => $product->id,
            'user_id'   => $request->user()->id
        ]);

        return response([
            'message' => 'Votre approvisionnement a été ajouté avec succès !',
            'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
        ],201);
    }

    public function addOutput(Request $request,Product $product){
        $product = Product::with(['productSupplier','category','productType'])->where('id',$product->id)->first();

        $request->validate([
            'quantite'   => 'required',
            'type'  => 'required',
            'motif'  => 'required',
        ]); 

        if($request->type === 'UNITE'){
            if($product->ProductType->name === 'VENDU_PAR_KG'){
                $nbreUnites =  ($product->qte_en_stock * $product->poids) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->poids);
                $resteUnites = $newNbreUnites % $product->poids;

            }else if($product->ProductType->name === 'VENDU_PAR_LITRE'){
                $nbreUnites =  ($product->qte_en_stock * $product->qte_en_litre) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->qte_en_litre);
                $resteUnites = $newNbreUnites % $product->qte_en_litre;

            }else if($product->ProductType->name === 'VENDU_PAR_NOMBRE_PAR_CONTENEUR'){
                $nbreUnites =  ($product->qte_en_stock * $product->nbre_par_carton) + $product->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$request->quantite;
                $totalResteEntiere = intval($newNbreUnites / $product->nbre_par_carton);
                $resteUnites = $newNbreUnites % $product->nbre_par_carton;

                
            }else if($product->ProductType->name === 'VENDU_PAR_PIECE'){
                return 'jamais';
            }

            if ($newNbreUnites >= 0) {
                // increment qte product
                $product->update([
                    'qte_en_stock' => $totalResteEntiere,
                    'is_stock'     => $newNbreUnites > 0,
                    'unite_restante' => $resteUnites
                ]);

                if ($totalResteEntiere <= $product->qte_stock_alert) {
                    // notification alert stock
                    // $product->notify(new ProductStockDangerNotification());
                }

                // new historic
                ProductHistory::create([
                    'quantite'  => $request->quantite,
                    'type'      => 'SORTIE',
                    'motif'     => $request->motif,
                    'product_id' => $product->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => $request->type === 'CARTON' ? false : true
                ]);

                return response([
                    "message" => 'The product was successfully redrawn !',
                    'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
                ],201);
            } else {
                return response([
                    "error" => "The quantity in stock of $product->name is insufficient !"
                ],201);
            }
        }else{
            if (((int)$product->qte_en_stock - (int)$request->quantite) >= 0){
                $newQteProduct = (int)$product->qte_en_stock -= (int)$request->quantite;
                // increment qte product
                $product->update([
                    'qte_en_stock' => $newQteProduct,
                    'is_stock'     =>  ($newQteProduct + $product->unite_restante) > 0 
                ]);

                if ($newQteProduct <= $product->qte_stock_alert) {
                    // notification alert stock
                    // $product->notify(new ProductStockDangerNotification());
                }

                // new historic
                ProductHistory::create([
                    'quantite'  => $request->quantite,
                    'type'      => 'SORTIE',
                    'motif'     => $request->motif,
                    'product_id' => $product->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => $request->type === 'CARTON' ? false : true
                ]);

                return response([
                    "message" => 'The product was successfully redrawn !',
                    'product' => Product::with(['productSupplier','category','ProductType'])->where('id',$product->id)->first()
                ],201);
                
            }else{
                return response([
                    "error" => "The quantity in stock of $product->name is insufficient !"
                ],201);
            }
        }

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
            $rules['image'] = 'required|mimes:png,jpg,jpeg,PNG,JPG,jpG,Jpg,jPg,jPG,JPEG,jfif,JFIF,avif,AVIF|max:8000';
    
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
            $rules['image'] = 'required|mimes:png,jpg,jpeg,PNG,JPG,JPEG,jfif,JFIF,avif,AVIF|max:4000';
    
            $filename = time() . '.' . $request->image->extension();
            $path = $request->image->storeAs('img/products', $filename, 'public');
        }

        if($request->qte_en_litre){
            $rules['qte_en_litre'] = 'required';
        }

        $data = $request->validate($rules);

        $data['image'] = $path ?? ($product->image ?? null);

        $product->update($data);

        return response([
            'message' => "Your product has been successfully updated !"
        ],201);
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