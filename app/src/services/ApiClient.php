<?php

namespace Helium\services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Helium\traits\LoggableTrait;
use Psr\Http\Message\StreamInterface;

class ApiClient
{
    use LoggableTrait;

    /**
     * @var Client
     */
    private $client;

    private $method = "POST";
    private $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];
    private $page = '';
    private $body = [];
    protected $responses = [];

    public function __construct(string $endpoint)
    {
        $this->setupLogging(static::class);
        $this->client = new Client(['base_uri' => $endpoint]);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    protected function makeRequest()
    {
        $options = [
            'headers' => $this->headers,
        ];

        if ($this->body) {
            $options['body'] = json_encode($this->body);
        }

        $this->log("Sending API request to '$this->page'", $this->body);
        try {
            $response = $this->client->request($this->method, $this->page, $options);
            $this->responses['code'] = $response->getStatusCode();
            $body = $response->getBody();
            if ($body instanceof StreamInterface) {
                $this->responses['contents'] = json_decode($body->getContents(), true);
            }
            $this->log("Completed API request:", $this->responses);
        } catch (GuzzleException $e) {
            $this->responses['code'] = $e->getCode();
            $this->responses['errorMessage'] = $e->getMessage();
            $this->log('GuzzleException: ', $e->getMessage(), 400);
            $this->log('GuzzleException: ', $e->getResponse()->getBody()->getContents(), 400);
        }
    }

    /**
     * @param string $phone
     *
     * @return bool|null
     */
    public function accountLookup(string $phone): ?bool
    {
        $this->page = "user/auth/lookup/";
        $this->body = ['phone' => $phone];
        $this->makeRequest();

        if ($this->responses['code'] !== 202) {
            return null;
        }

        return $this->responses['contents']['success'] ?? false;
    }

    /**
     * @param string $orderID
     *
     * @return bool|null
     */
    public function orderLookup(string $orderID): ?bool
    {
        $this->page = "orders/{$orderID}";
        $this->method = "GET";
        $this->makeRequest();

        if ($this->responses['code'] !== 202) {
            return null;
        }

        return $this->responses['contents']['success'] ?? false;
    }

}