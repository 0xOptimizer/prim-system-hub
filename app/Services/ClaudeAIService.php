<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class ClaudeAIService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('CLAUDE_API_KEY');

        if (empty($this->apiKey)) {
            throw new Exception('Claude API key is missing. Check your .env file.');
        }
    }

    public function getResponse($messages)
    {
        $response = $this->client->post($this->apiUrl, [
            'headers' => [
                'x-api-key' => trim($this->apiKey),
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'claude-3-7-sonnet-latest',
                'messages' => $messages,
                'max_tokens' => 4096,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}