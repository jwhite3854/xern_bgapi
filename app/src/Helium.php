<?php

namespace Helium;

use Exception;
use Helium\Core\Authorize;
use Helium\Core\Router;

class Helium
{
    const ERROR_GENERAL = 'Error! Unable to Render Page.';
    const ERROR_NO_URL = 'Error! Cannot find the URL.';

    protected $request;
    private static $instance;
    private $routes;
    private $configs;

    public function __construct()
    {
        $this->setConfigs();
        $this->setRoutes();
    }

    public static function getInstance(): Helium
    {
        if ( self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Start the process...
     * @param string $request
     */
    public static function run(string $request)
    {
        $helium = self::getInstance();

        $helium->request = $request;
        $controllerOutput = self::ERROR_GENERAL;

        try {
            //GET Controller / Action / Params from request
            $router = new Router($helium->routes, $request);
            $controllerName = $router->getController();
            $actionName = $router->getAction();
            $params = $router->getParams();

            // Get the path of the controller
            $controllerPath = APP_ROOT . DS . 'src' . DS . 'controllers' . DS . $controllerName.'.php';
            $controllerOutput = self::ERROR_NO_URL;

            //Does the controller file exist?
            if (file_exists( $controllerPath )) {
                require $controllerPath;
                // Does the controller class exist?
                $controllerClassName = 'Helium\controllers\\' . $controllerName;
                if ( class_exists( $controllerClassName ) ) {
                    // Create the new controller class with the action and parameters
                    $modelName = str_replace('Controller', '', $controllerName);
                    $controllerObject = new $controllerClassName($modelName, $actionName, $params );
                    if ($controllerObject::REQUIRES_AUTH) {
                        unset($params['authorized_user']);
                        $authorize = new Authorize();
                        if ($user = $authorize->getUser()) {
                            $params['authorized_user'] = $user;
                        } else {
                            $actionName = 'error403';
                        }
                    }

                    if ( method_exists($controllerObject, $actionName) ) {
                        // Get the output of the controller of the given action with the params provided
                        $controllerOutput = $controllerObject->$actionName( $params );
                    } else {
                        $helium->printError( 'Cannot find '.$controllerName.' method: '. $actionName, 43);
                    }
                } else {
                    $helium->printError( 'Cannot find controller class: ' .$controllerClassName, 42);
                }
            } else {
                $helium->printError( 'Cannot find controller file: ' . $controllerPath, 41);
            }
        } catch ( Exception $e ) {
            if ( self::getConfig('DEV_MODE') ) {
                $helium->printException( $e );
            }
        } finally {
            echo $controllerOutput;
        }
    }

    /**
     * Get Global Config setting
     * @param string $key
     * @return string|null
     */
    public static function getConfig(string $key): ?string
    {
        $helium = self::getInstance();

        return $helium->configs[$key] ?? null;
    }

    /**
     * @return string|false
     */
    public static function getAuthToken()
    {
        $headers = getallheaders();
        if ($auth = $headers['Authorization'] ?? false) {
            preg_match('/Bearer\s([0-9a-f]{32}\.[0-9a-f]{32})/', $auth, $matches);
            if (count($matches) === 2) {
                return $matches[1];
            } else {
                return false;
            }
        }

        return $auth;
    }

    /**
     * Prints exceptions for debugging
     * @param $e
     */
    private function printException( $e )
    {
        echo '<pre>';
        echo $e->getMessage() . "\n\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    }

    /**
     * Prints error for debugging
     * @param string $error
     * @param int $errorCode
     */
    private function printError(string $error, int $errorCode = 50): void
    {
        header('Content-Type: application/json', true, 500);

        $output = [
            'code' => $errorCode,
            'message' => $error,
        ];

        echo json_encode($output);
    }

    /**
     * Gets all routes for the app
     */
    private function setConfigs(): void
    {
        $configs = [];
        if (file_exists(APP_ROOT . DS . 'env.local.txt')) {
            $content = file_get_contents(APP_ROOT . DS . 'env.local.txt');
            $configsData = explode("\n", $content);
            foreach ($configsData as $line) {
                $parts = explode("=", $line);
                $configs[$parts[0]] = $parts[1] ?? "";
                if (strtolower($parts[1]) === "true" || strtolower($parts[1]) === "false") {
                    $configs[$parts[0]] = (strtolower($parts[1]) === "true");
                }
            }
        }

        $this->configs = $configs;
    }

    /**
     * Gets all routes for the app
     */
    private function setRoutes(): void
    {
        $routes = [];
        if (file_exists(APP_ROOT . DS . 'routes.json')) {
            $content = file_get_contents(APP_ROOT . DS . 'routes.json');
            $routesData = json_decode($content, true) ?? [];
            foreach ($routesData as $controller => $actions) {
                foreach ($actions as $action => $isVisible) {
                    $key = $controller . DS . $action;
                    $routes[$key] = [
                        'controller' => $controller,
                        'action'     => $action,
                        'visible'    => $isVisible
                    ];
                }
            }
        }

        $this->routes = $routes;
    }
}