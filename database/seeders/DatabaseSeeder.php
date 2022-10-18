<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        \App\Models\Company::factory(4)->create();
        \App\Models\User::factory(100)->create();
        \App\Models\User::factory()->create([
            'firstname' => 'All',
            'lastname' => 'H corp',
            'email' => 'info@allhcorp.com',
            'role' => 'SUPER',
            'as_company' => false
        ]);
        \App\Models\Customer::factory(10)->create();

        \App\Models\ProductSupplier::factory(1)->create([
            'name' => "Alibaba",
        ]);
        \App\Models\ProductSupplier::factory(1)->create([
            'name' => "Amazon",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Produit de plomberie",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Electroménager",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Electronique",
        ]);
        \App\Models\Category::factory(1)->create([
            'name' => "Nutrition",
        ]);
         \App\Models\ProductType::factory(1)->create([
            'name' => "VENDU_PAR_PIECE",
            'slug' => 'Vendu par pièce',
            'unite_de_mesure' => 'KG',
         ]);
         \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par kg/g",
            'name' => "VENDU_PAR_KG",
            'unite_de_mesure' => 'KG',
         ]);
         \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par litre",
            'name' => "VENDU_PAR_LITRE",
            'unite_de_mesure' => 'L',
         ]);
         \App\Models\ProductType::factory(1)->create([
            'slug' => "Vendu par nombre par conteneur",
            'name' => "VENDU_PAR_NOMBRE_PAR_CONTENEUR",
            'unite_de_mesure' => 'KG',
         ]);

         \App\Models\Product::factory(1)->create([
            'name' => "MacBook Pro - 2022",
            'qte_en_stock' => '15',
            'prix_unitaire' => 2400000,
            'type_approvisionnement' => 'Pièce',
            'category_id' => 3,
            'product_supplier_id' => 1,
            'product_type_id' => 1,
            'poids' => 0.5,
            'qte_stock_alert' => 3,
            'is_stock' => true
         ]);

         \App\Models\Product::factory(1)->create([
            'name' => "Frigo samsung ",
            'qte_en_stock' => 8,
            'prix_unitaire' => 650000,
            'type_approvisionnement' => 'Pièce',
            'category_id' => 2,
            'product_supplier_id' => 2,
            'product_type_id' => 1,
            'poids' => 0.5,
            'qte_stock_alert' => 6,
            'is_stock' => true
         ]);

    }
}
