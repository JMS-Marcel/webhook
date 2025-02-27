<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/webhook', function (Request $request) {
    // Validation du webhook
    if ($request->hub_verify_token === 'mon_token_secret') {
        return response($request->hub_challenge, 200);
    }
    return response('Token invalide', 403);
});