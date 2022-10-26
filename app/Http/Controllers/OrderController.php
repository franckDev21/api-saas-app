<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\TotalCash;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Order::with(['orderProducts','customer','user'])->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // on creer la commande
        $commande = Order::create([
            'reference' => time(),
            'quantite'  => $request->total_qte,
            'desc'      => $request->desc,
            'cout'      => $request->totalCommande,
            'customer_id' => $request->client,
            'user_id'   => $request->user()->id,
            'etat'      => 'IMPAYER'
        ]);

        // on créé la facture
        Invoice::create([
            'customer_id'     => $request->client,
            'order_id'   => $commande->id
        ]);

        foreach($request->carts as $cart){
            OrderProduct::create([
                'order_id' => $commande->id,
                'product_id'  => $cart['id'],
                'qte'         => $cart['qte'],
                'prix_de_vente' => $cart['prix_de_vente'],
                'type_de_vente' => $cart['type_de_vente']
            ]);
        }

         // on met a jour le stock
         foreach($request->carts as $cart){
            $cart = (object)$cart;

            $newNbreParCarton = 0;
                        
            if($cart->product_type['name'] === 'VENDU_PAR_KG'){
                $nbreUnites =  ($cart->qte_en_stock * $cart->poids) + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / $cart->poids);
                $resteUnites = $newNbreUnites % $cart->poids;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  $cart->poids;
                }

            }else if($cart->product_type['name'] === 'VENDU_PAR_LITRE'){
                $nbreUnites =  ($cart->qte_en_stock * $cart->qte_en_litre) + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / $cart->qte_en_litre);
                $resteUnites = $newNbreUnites % $cart->qte_en_litre;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  $cart->qte_en_littre;
                }

            }else if($cart->product_type['name'] === 'VENDU_PAR_NOMBRE_PAR_CONTENEUR'){
                $nbreUnites =  ($cart->qte_en_stock * $cart->nbre_par_carton) + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $totalResteEntiere = intval($newNbreUnites / $cart->nbre_par_carton);
                $resteUnites = $newNbreUnites % $cart->nbre_par_carton;
                if($cart->type_de_vente === 'PIECE'){
                    $resteUnites =  $nbreUnites %  $cart->nbre_par_carton;
                }
                
            }else if($cart->product_type['name'] === 'VENDU_PAR_PIECE'){
                $nbreUnites = $cart->qte_en_stock + $cart->unite_restante;
                $newNbreUnites = $nbreUnites - (int)$cart->qte;
                $newNbreParCarton = $newNbreUnites;
                $resteUnites = 0;
            }

            if ($newNbreUnites >= 0) {
                // increment qte product
                $product = Product::find($cart->id);

                if($cart->type_de_vente === 'PIECE'){
                    $newNbreParCarton = (int)$cart->qte_en_stock - (int)$cart->qte;
                }

                $product->update([
                    'qte_en_stock' => $newNbreParCarton,
                    'is_stock'     => $newNbreUnites > 0,
                    'reste_unites' => $resteUnites
                ]);

                // if ($newNbreParCarton <= $product->qte_stock_alert) {
                //     $product->notify(new ProductStockDangerNotification());
                // }

                // new historic
                ProductHistory::create([
                    'quantite'  => $cart->qte,
                    'type'      => 'SORTIE',
                    'motif'     => 'Commande',
                    'product_id'=> $cart->id,
                    'user_id'   => $request->user()->id,
                    'is_unite'  => true
                ]);

            } else {
                return response()->json(["error", 'Stock inssufisant !']);
            }
        }

        // Send an email to the user

         return response([
            "message"  =>  "Your order has been successfully registered",
            "order_id" => $commande->id
        ],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order)
    {
        $order = Order::with(['orderProducts','customer','user'])->where('id',$order->id)->latest()->first();
        $products = $order->orderProducts;
        $newProductsTab =  [];

        foreach($products as $product){
            $newProductsTab[] = [
                'id' => $product->id,
                'order_id' => $product->order_id,
                'prix_de_vente' => $product->prix_de_vente,
                'product_id' => $product->product_id,
                'qte' => $product->qte,
                'type_de_vente' => $product->type_de_vente,
                'updated_at' => $product->updated_at,
                'created_at' => $product->created_at,
                'product' => Product::find($product->product_id),
            ];
        }

        return response([
            'order'    => $order,
            'products' => $newProductsTab
        ],201);
    }

    public function getInvoice(Order $order){
        $invoice = Invoice::with(['order','customer'])->where('order_id',$order->id)->first();
        return $invoice;
    }

    public function payer(Request $request,Order $order){

        if($order->etat === 'PAYER'){
            return response([
                'error' => "This order has already been paid"
            ]);
        }

        // on met a jour l'etat de la order
        $order->update([
            'etat' => 'PAYER'
        ]);

        // on met a jour la caisse
        Cash::create([
            'user_id' => $request->user()->id,
            'type' => 'ENTRER',
            'montant' => (int)implode('',explode('.',$order->cout)),
            'order_id' => $order->id,
            'motif'   => 'Payment of the order'
        ]);

        $caisse = TotalCash::first();

        if(!$caisse){
            $caisse = TotalCash::create([
                'montant' => 0
            ]);
        }

        $total = $caisse->sum('montant');

        $caisse->update([
            'montant' => (int)$total + (int)implode('',explode('.',$order->cout))
        ]);

        return response([
            "message" => "The order has been successfully registered"
        ],201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        if($order->statut !== 'PAYER'){
            $order->delete();
            return response([
                "message" => "the order has been successfully deleted!"
            ],201);
        }else{
            return response([
                "error" => "The order has already been paid"
            ],201);
        }
    }
}
