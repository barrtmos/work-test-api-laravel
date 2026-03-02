<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testGet(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|min:2|max:50',
            'age'  => 'nullable|integer|min:18|max:99',
        ]);

        return response()->json([
            'success' => true,
            'method' => 'GET',
            'received' => $validated
        ]);
    }

    public function testPost(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'event' => 'required|in:PageView,Lead,Purchase',
        ]);

        return response()->json([
            'success' => true,
            'method' => 'POST',
            'received' => $validated
        ]);
    }
}
