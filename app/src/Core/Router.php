<?php

namespace Helium\Core;

use Helium\Helium;

class Router
{
    protected $uri;             // Uri
    protected $controller;      // Request controller
    protected $action;          // Request action
    protected $params;          // Request Parameters
    protected $isVisible = true;          // Request Parameters
    protected $isSecure = false;          // Request Parameters

    /**
     * Router constructor.
     * @param array $routes
     * @param $uri
     */
    public function __construct(array $routes, &$uri)
    {
        //Load Defaults
        $this->controller = 'default';
        $this->action = 'index';

        // Get the real $uri set in routes config
        $host = parse_url(Helium::getConfig('DOMAIN'), PHP_URL_HOST);
        $redirect_base = Helium::getConfig('REDIRECT_BASE');
        if ( !empty($redirect_base) && $redirect_base !== '/' ) {
            $uri = str_replace( $redirect_base, '', $uri );
        }

        $this->uri = urldecode(trim(strtolower($uri), '/'));

        //Get Params
        $query = parse_url( $this->uri, PHP_URL_QUERY);
        parse_str($query, $this->params);

        // If uri is empty, we're either on the homepage or an error has occurred
        if ( empty( $this->uri ) ) {
            return;
        }

        $this->action = 'error404';

        // Get uri path
        $uri_path = parse_url( $this->uri, PHP_URL_PATH );
        $possibleRoutes = [$uri_path];

        // If uri is found in array of established routes, set the controller/action
        $parts = explode('/', $uri_path);
        if (count($parts) === 1) {
            $possibleRoutes[] = $uri_path . DS . 'index';
            $possibleRoutes[] = 'default' . DS . $uri_path;
        }

        foreach ($possibleRoutes as $route) {
            if ( array_key_exists( $route, $routes ) ) {
                $this->controller = $routes[$route]['controller'];
                $this->action = $routes[$route]['action'];
                if (
                    !$routes[$route]['visible'] &&
                    parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $host
                ) {
                    die();
                }
                break;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller.'Controller';
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }
}