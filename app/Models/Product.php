<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
        'category_id' => 3,
        'product_supplier_id' => 1,
        'product_type_id' => 1,
    */

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function productSupplier(){
        return $this->belongsTo(ProductSupplier::class);
    }

    public function productType(){
        return $this->belongsTo(ProductType::class);
    }
}
