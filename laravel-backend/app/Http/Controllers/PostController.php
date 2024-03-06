<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    // http://127.0.0.1:8000/api/post/create
    public function create(){
        return response()->json([
            'status' => true,
            'response' => [
                'data' => [
                    'id' => '123',
                    'text' => 'Some text',
                ]
            ],
            'error' => [
                'message' => 'error message'
            ]
        ], 200);
    }
}
