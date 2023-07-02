<?php

namespace Helium\traits;

use Helium\Core\Request;
use Helium\Core\Response;

trait ApiTrait
{
    public function index(): Response
    {
        $filters = [];
        $id = $this->getRequestID();
        switch ($this->getMethod()) {
            case Request::METHOD_GET;
                if ($id) {
                    $data = $this->getOne($id);
                } else {
                    $data = $this->getAll($filters);
                }
                break;
            case Request::METHOD_POST;
                $data = [];
                break;
            case Request::METHOD_PUT:
                $data = $this->getOne($id);
                break;
            case Request::METHOD_PATCH;
                $data = $this->getAll($filters);
                break;
            default:
                return $this->respondIncompatibleMethod();
        }

        return $this->respond($data);
    }

    public function delete(): Response
    {
        if ($this->getMethod() === Request::METHOD_DELETE) {
            return $this->respondIncompatibleMethod();
        }

        return $this->respond([]);
    }

    private function getAll(array $filters): array
    {
        return $filters;
    }

    private function getOne(int $id): array
    {
        return [$id];
    }

    /**
     * Render controller, Web view is rendered if no path specified.
     *
     * @param array $data
     * @param int $response_code
     * @param array $addl_headers
     * @return Response
     */
    private function respond(array $data = [], int $response_code = 200, array $addl_headers = []): Response
    {
        return new Response($data, $response_code, $addl_headers);
    }

    /**
     * @param array $headers
     * @return Response
     */
    private function respondIncompatibleMethod(array $headers = []): Response
    {
        return new Response([], 500, $headers);
    }
}