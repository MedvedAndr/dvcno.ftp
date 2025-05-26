<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;
// use Illuminate\Http\JsonResponse;

class CookieController extends Controller {
    public function setCookie(Request $request) {
        $key   = $request->input('key');
        $value = $request->input('value');

        Cookie::queue(Cookie::forever($key, $value));

        return response()->json([
            'status'  => 'success',
            'message' => 'Cookie успешно установлено навсегда',
            'key'     => $key,
            // 'debug'   => response()->headers->all(),
        ]);
    }
}