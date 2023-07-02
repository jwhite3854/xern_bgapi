<?php

namespace Helium\Core;

class Controller
{
    public const REQUIRES_AUTH = true;

    protected $model;
    protected $action;
    protected $params;

    /**
     * Controller constructor.
     * @param string $model
     * @param string $action
     * @param array $params
     */
    public function __construct(string $model, string $action, array $params = [])
    {
        $this->model = $model;
        $this->action = $action;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Render controller, Web view is rendered if no path specified.
     *
     * @param array $data
     * @param array $headers
     * @return string
     */
    function render(array $data = [], array $headers = []): string
    {
        if ($status = $headers['status'] ?? false) {
            unset($headers['status']);
        } else {
            $status = 200;
        }

        //Render Full Layout
        $response = new Response($data, $status, $headers);

        return $response->send();
    }

    public function error403(): string
    {
        return $this->render(['code' => 403, 'message' => 'this resource is restricted'], ['status' => 403]);
    }

    public function error404(): string
    {
        return $this->render(['code' => 404, 'message' => 'this resources does not exist'], ['status' => 404]);
    }
}