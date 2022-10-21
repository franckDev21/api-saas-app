<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantite',
        'type',
        'motif',
        'old_state_stock',
        'is_unite',
        'product_id',
        'user_id'
    ];
}
