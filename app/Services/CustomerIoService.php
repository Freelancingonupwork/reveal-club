<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CustomerIoService
{
    protected $client;
    protected $siteId;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://track.customer.io/api/v1/']);
        $this->siteId = env('CUSTOMER_IO_SITE_ID');
        $this->apiKey = env('CUSTOMER_IO_API_KEY');
    }

    // Function to add or update customer
    public function addOrUpdateCustomer($customerId, $data, $segmentId)
    {
        try {
            $response = $this->client->put("customers/{$customerId}", [
                'auth' => [$this->siteId, $this->apiKey],
                'json' => $data
            ]);
            
            $this->addCustomerToSegment($segmentId, $customerId);

            return $response;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Function to add customer to a segment
    public function addCustomerToSegment($segmentId, $customerId)
    {
        try {
            $response = $this->client->post("segments/{$segmentId}/add_customers", [
                'auth' => [$this->siteId, $this->apiKey],
                'json' => ['ids' => [$customerId]]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function triggerEmailBroadcast($broadcastId, $recipients)
    {
        try {
            $response = $this->client->post("campaigns/{$broadcastId}/send", [ // Update this to the correct endpoint
                'auth' => [$this->siteId, $this->apiKey],
                'json' => [
                    'recipients' => $recipients,
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            // Handle errors
            return ['error' => $e->getMessage()];
        }
    }

    // Function to send transactional email
    public function sendTransactionalEmail($email, $messageId, $data = [])
    {
        try {

            $requestData = [
                "transactional_message_id" => $messageId,
                "identifiers" => [
                    "email" => $email
                ],
                "to" => $email,
                "message_data" => $data
            ];
            // dd($requestData);
            $client = new Client();
            
            $response = $client->post('https://api-eu.customer.io/v1/send/email', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer 6f52b128b2cf5b739b6c7272ceaaa4c3', // reveal app api key
                ],
                'json' => $requestData,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error("Failed to send transactional email: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    // Function to update customer attributes
    public function updateCustomer($customerId, $email, $attributes = [])
    {
        try {
            $response = $this->client->put("customers/{$customerId}", [
                'auth' => [$this->siteId, $this->apiKey],
                'json' => [
                    'email' => $email,
                    'attributes' => $attributes
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error("Failed to update customer: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
