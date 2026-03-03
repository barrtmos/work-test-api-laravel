<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestGetRequest;
use App\Http\Requests\TestPostRequest;

class TestController extends Controller
{
    public function testGet(TestGetRequest $request)
    {
        $validated = $request->validated();

        return response()->json([
            'success'  => true,
            'method'   => 'GET',
            'received' => $validated
        ]);
    }

    public function testPost(TestPostRequest $request)
    {
        $validated = $request->validated();

        return response()->json([
            'success'  => true,
            'method'   => 'POST',
            'received' => $validated
        ]);
    }
}
