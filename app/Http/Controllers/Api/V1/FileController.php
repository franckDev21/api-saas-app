<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FileController extends Controller
{ 
    public function generateBase64(Request $request){
        $request->validate([
            'image' => 'required'
        ]);
        return  base64_encode($request->image);
    }
}
