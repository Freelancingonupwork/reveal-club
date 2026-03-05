<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class OpenFoodFactsService
{
    protected $baseUrl = 'https://world.openfoodfacts.org';

    public function searchRecipes($query, $page = 1, $pageSize = 20)
    {
        $params = [
            'search_terms' => $query,
            'search_simple' => 1,
            'action' => 'process',
            'json' => 1,
            'page' => $page,
            'page_size' => $pageSize,
            'lc' => 'en',
            'fields' => 'product_name,code', // Get only necessary fields
        ];

        try {
            $response = Http::retry(3, 100) // Retry 3 times, waiting 100 milliseconds between attempts
                           ->timeout(10) // Set a timeout of 10 seconds
                           ->get($this->baseUrl . '/cgi/search.pl', $params);

            return $response->json();
        } catch (RequestException $e) {
            // Handle the exception or log it as needed
            return null;
        }
    }

    public function getProductDetails($barcode)
    {
        try {
            $response = Http::retry(3, 100) // Retry 3 times, waiting 100 milliseconds between attempts
                           ->timeout(10) // Set a timeout of 10 seconds
                           ->get($this->baseUrl . '/api/v0/product/' . $barcode . '.json');

            return $response->json();
        } catch (RequestException $e) {
            // Handle the exception or log it as needed
            return null;
        }
    }
}
