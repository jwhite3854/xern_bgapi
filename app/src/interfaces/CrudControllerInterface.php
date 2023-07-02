<?php

namespace Helium\interfaces;

use Exception;

interface CrudControllerInterface
{
    /** @throws Exception */
    function getOne(int $id): array;

    /** @throws Exception */
    function getAllBy(array $filters): array;

    /** @throws Exception */
    function create(array $data): array;

    /** @throws Exception */
    function update(array $data): array;

    /** @throws Exception */
    function patch(array $data, int $id): array;

    /** @throws Exception */
    function remove(int $id): bool;
}