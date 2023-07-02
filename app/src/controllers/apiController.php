<?php

namespace Helium\controllers;

use Helium\Core\Controller;

/**
 * @Route("/api")
 */
class apiController extends Controller
{
    /**
     * @Route("/toggle" name="api_toggle")
     */
    public function toggle(){
        return json_encode($_SERVER);
    }
}