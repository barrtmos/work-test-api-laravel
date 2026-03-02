<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    // Обработка GET с параметрами в URL
    public function testGet(Request $request)
    {
        return response()->json([
            'success' => true,
            'method' => 'GET',
            'received_query' => $request->query() // name и age упадут сюда автоматически
        ]);
    }

    // Обработка POST с JSON-телом
    public function testPost(Request $request)
    {
        return response()->json([
            'success' => true,
            'method' => 'POST',
            'received_data' => $request->all() // email и event извлекутся из JSON
        ]);
    }
}
