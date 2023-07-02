<?php

namespace Helium\Core;

class Request
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';

    private $posts = [];
    private $queries = [];
    private $method = 'GET';
    private $cookies = [];
    private $files = [];
    private $headers = [];
    private $uri = '';
    private $id = 0;

    public function __construct(array $request)
    {
        $this->posts = $_POST ?? [];
        $this->queries = $_GET ?? [];
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->cookies = $_COOKIE ?? [];
        $this->files = $_FILES ?? [];
        $this->headers = getallheaders();
        $this->parseUri($request);
    }

    private function parseUri(array $request): void
    {
        $this->uri = $request["REQUEST_URI"] ?? '';
        $this->id = $request["REQUEST_ID"] ?? 0;

        if (isset($request['REQUESTED_ACTION'])) {
            $this->uri .= '/' . $request['REQUESTED_ACTION'];
        }
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosts(?string $key = null)
    {
        if ($key) {
            return $this->posts[$key] ?? null;
        }

        return $this->posts;
    }

    public function getQuery(?string $key = null)
    {
        if ($key) {
            return $this->queries[$key] ?? null;
        }

        return array_filter($this->queries, function($k) {
           return !in_array($k, ['REQUEST_URI', 'REQUEST_ID', 'REQUESTED_ACTION']);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCookies(?string $key = null)
    {
        if ($key) {
            return $this->cookies[$key] ?? null;
        }

        return $this->cookies;
    }

    public function getFiles(?string $key = null)
    {
        if ($key) {
            return $this->files[$key] ?? null;
        }

        return $this->files;
    }

    public function getHeaders(?string $key = null)
    {
        if ($key) {
            return $this->headers[$key] ?? null;
        }

        return $this->headers;
    }
}