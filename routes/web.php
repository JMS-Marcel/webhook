<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/webhook', function (Request $request) {
    // Validation du webhook
    if ($request->hub_verify_token === '7KzqO92eRt5OIJq9jHt1rYskD62Te3fxvWm') {
        return response($request->hub_challenge, 200);
    }
    return response('Token invalide', 403);
});

Route::post('/webhook', function (Request $request) {
    $data = $request->all();

    // Vérifie s'il s'agit d'un message
    if (isset($data['entry'][0]['messaging'][0]['message'])) {
        $message = $data['entry'][0]['messaging'][0]['message']['text'];
        $senderId = $data['entry'][0]['messaging'][0]['sender']['id'];

        // Logique pour traiter le message
        Log::info("Message reçu : $message de l'utilisateur $senderId");

        // Appelle l'API d'IA et renvoie une réponse
        $reply = callAIApi($message);
        sendToMessenger($senderId, $reply);
    }

    return response('OK', 200);
});

function callAIApi($message) {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('sk-d023842b01b3411cb5f8158d1051af01'),
    ])->post('	https://api.deepseek.com', [
        'message' => $message,
    ]);

    return $response->json()['reply'];
}

function sendToMessenger($senderId, $message) {
    $pageAccessToken = env('FACEBOOK_PAGE_ACCESS_TOKEN');

    $response = Http::post("https://graph.facebook.com/v13.0/me/messages?access_token=$pageAccessToken", [
        'recipient' => ['id' => $senderId],
        'message' => ['text' => $message],
    ]);

    if ($response->successful()) {
        Log::info('Message envoyé avec succès !');
    } else {
        Log::error('Erreur lors de l\'envoi du message : ' . $response->body());
    }
}