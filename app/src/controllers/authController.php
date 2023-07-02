<?php

namespace Helium\controllers;

use Helium\Core\Controller;
use Helium\Helium;

class authController extends Controller
{
    public const REQUIRES_AUTH = false;

    public function register(): string
    {

    
        return $this->render([]);
    }
}