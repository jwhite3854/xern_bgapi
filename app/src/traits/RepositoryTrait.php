<?php

namespace Helium\traits;

use Exception;
use Helium\exceptions\EmptyResultException;
use Helium\services\Connection;

trait RepositoryTrait
{
    private $table = '';
    private $id_field = '';

    private function getConnection(): Connection
    {
        return new Connection();
    }

    /**
     * @throws Exception
     */
    public function find(int $id): array
    {
        if (empty($this->table) || empty($this->id_field)) {
            throw new Exception('');
        }

        $query = "SELECT * FROM {$this->table} WHERE {$this->id_field} = :{$this->id_field}";
        $params = [$this->id_field => $id];

        $conn = new Connection();
        $result = $conn->doQuery($query, $params, Connection::FETCH_ONE);
        if (empty($result))  {
            throw new EmptyResultException('Unable to find by ID ' . $id);
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    private function findBy(array $params): array
    {
        if (empty($this->table) || empty($this->id_field)) {
            throw new Exception('');
        }

        $query = "SELECT * FROM {$this->table}";
        if (!empty($filters)) {
            $wheres = [];
            foreach ($params as $param => $value) {
                $wheres[] = $param . ' = :' . $param;
            }
            $query .= " WHERE " . implode(' AND ', $wheres);
        }

        $conn = new Connection();
        $result = $conn->doQuery($query, $params);
        if (empty($result))  {
            throw new EmptyResultException('Unable to find any');
        }

        return $result;
    }
}