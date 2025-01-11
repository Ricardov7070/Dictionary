<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class wordsApiProxyController extends Controller
{
    public function fetchWordDetails ($word): JsonResponse {

        $baseUrl = 'https://wordsapiv1.p.rapidapi.com/words/';

        try {
            
            $response = Http::withHeaders([
                'x-rapidapi-host' => 'wordsapiv1.p.rapidapi.com',
                'x-rapidapi-key' => env('WORDS_API_KEY'), 
            ])->get($baseUrl . $word);

            return response()->json($response->json(), $response->status());

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch data from Words API.',
                'message' => $e->getMessage()
            ], 400);

        }

    }
    
}
