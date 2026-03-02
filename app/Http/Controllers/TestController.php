<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testGet(Request $request)
    {
        return response()->json([
            'success' => true,
            'method' => 'GET',
            'received_query' => $request->query()
        ]);
    }

    public function testPost(Request $request)
    {
        return response()->json([
            'success' => true,
            'method' => 'POST',
            'received_data' => $request->all()
        ]);
    }
}
