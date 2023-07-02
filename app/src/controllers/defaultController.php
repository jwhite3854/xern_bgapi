<?php

namespace Helium\controllers;

use Helium\Core\Controller;
use Helium\Helium;

class defaultController extends Controller
{
    public const REQUIRES_AUTH = false;

    public function index(): string
    {
        $layoutData = $this->generateGenericTitle($_SERVER["REQUEST_URI"]);
    
        return $this->render($layoutData);
    }

    private function generateGenericTitle($request_uri): array
    {
        $uri =  trim($request_uri, '/');
        $parts = array_reverse(explode('/', $uri));
        $title = ucwords(str_replace('-', ' ', $parts[0]));

        return [
            'meta' => [
                'title' => $title
            ]
        ];
    }
}