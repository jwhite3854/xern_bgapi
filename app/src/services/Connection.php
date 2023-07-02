<?php

namespace Helium\services;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAl\Connection as DocConnection;
use Doctrine\DBAL\Exception as DocException;
use Doctrine\DBAL\Driver\Exception as DocDriverException;
use Doctrine\DBAL\Driver\PDO\Result;
use Doctrine\DBAL\Driver\PDO\Statement as PDO_Statement;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Driver\Statement as D_Statement;
use Helium\Helium;


class Connection
{
    public const FETCH_ALL = 1;
    public const FETCH_ONE = 2;
    public const FETCH_COL = 3;
    public const FETCH_VAL = 4;

    /**
     * @var DocConnection
     */
    private $conn;

    /**
     * @throws DocException
     */
    public function __construct()
    {
        $params = [
            'driver'        => Helium::getConfig('MYSQL_DRIVER'),
            'user'          => Helium::getConfig('MYSQL_USER'),
            'password'      => Helium::getConfig('MYSQL_PASSWORD'),
            'host'          => Helium::getConfig('MYSQL_HOST'),
            'dbname'        => Helium::getConfig('MYSQL_DBNAME'),
            'charset'       => Helium::getConfig('MYSQL_CHARSET'),
            'driverClass'   => Helium::getConfig('MYSQL_DRIVER_CLASS'),
        ];

        $this->conn = DriverManager::getConnection($params);
    }

    public function getConn(): DocConnection
    {
        return $this->conn;
    }

    /**
     * @param string $table
     * @param array $parameterSets
     * @param bool $ignore
     *
     * @return int|null
     */
    public function doInsert(string $table, array $parameterSets, bool $ignore = false): ?int
    {
        $inserts = [];
        $params = [];
        if (!array_key_exists(0, $parameterSets)) {
            $keys = array_keys($parameterSets);
            $params = $parameterSets;
            $inserts[] = '(:' . implode(',:', $keys) . ')';
        } else {
            $keys = array_keys($parameterSets[0]);
            $length = count($parameterSets);
            for ($ii = 0; $ii < $length; $ii++) {
                foreach ($parameterSets[$ii] as $key => $value) {
                    $params[$key . $ii] = $value;
                    $inserts[] = '(:' . implode($ii . ',:', $keys) . $ii . ')';
                }
            }
        }

        $query = "INSERT " . ($ignore ? 'IGNORE ' : '') . "INTO $table (" . implode(',', $keys) . ') VALUES ';
        $query .= implode(', ', $inserts);

        try {
            $statement = $this->conn->prepare($query)->getWrappedStatement();

            foreach ($params as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }

            $results = $statement->execute();

            return $results->rowCount();
        } catch (DocDriverException|DocException $e) {
            return null;
        }
    }

    /**
     * @param string $table
     * @param array $parameters
     * @param array $constraints
     *
     * @return int|null
     */
    public function doUpdate(string $table, array $parameters, array $constraints): ?int
    {
        $setUpdates = [];
        $whereUpdates = [];
        $params = $parameters;

        foreach ($parameters as $key => $value) {
            $setUpdates[] = "$key = :$key";
        }

        foreach ($constraints as $key => $value) {
            $params['w_' . $key] = $value;
            $whereUpdates[] = "$key = :w_$key";
        }

        $query = "UPDATE $table SET " . implode(", ", $setUpdates) . " WHERE " . implode(" AND ", $whereUpdates);

        try {
            $statement = $this->conn->prepare($query)->getWrappedStatement();
            foreach ($params as $key => $value) {
                $statement->bindValue(':' . $key, $value);
            }

            $statement->execute();

            return $statement->rowCount();
        } catch (DocDriverException|DocException $e) {
            return null;
        }
    }

    /**
     * @param string $query
     * @param array $parameters
     * @param int $mode
     *
     * @return mixed|null
     */
    public function doQuery(string $query, array $parameters = [], int $mode = self::FETCH_ALL): ?array
    {
        try {
            $statement = $this->createStatement($query, $parameters);
            $results = $statement->execute();

            if (!$results instanceof Result) {
                return null;
            }

            return $this->fetchResults($results, $mode);
        } catch (DocDriverException $e) {
            return null;
        }
    }

    /**
     * @param string $query
     * @param array $parameters
     * @return D_Statement|null
     */
    private function createStatement(string $query, array $parameters = []): ?D_Statement
    {
        try {
            $params = $this->prepareParameters($parameters, $query);

            $statement = $this->conn->prepare($query)->getWrappedStatement();
            foreach ($params as $key => $value) {
                $statement->bindValue($key, $value);
            }

            return $statement;
        } catch (DocException | DocDriverException $e) {
            var_dump($e->getMessage());
            return null;
        }
    }

    /**
     * @param array $parameters
     * @param string $query
     *
     * @return array
     */
    private function prepareParameters(array $parameters, string &$query): array
    {
        $params = [];
        if (count($parameters) > 0) {
            foreach ($parameters as $key => $value) {
                if (is_array($value)) {
                    $keys = [];
                    $length = count($value);
                    for ($ii = 0; $ii < $length; $ii++) {
                        $keys[] = ':' . $key . $ii;
                        $params[$key . $ii] = $value[$ii];
                    }
                    $keyString = implode(',', $keys);
                    $query = str_replace(':' . $key, $keyString, $query);
                } else {
                    $params[$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * @param Result $statementResult
     * @param int $mode
     *
     * @return mixed|null
     */
    private function fetchResults(Result $statementResult, int $mode = self::FETCH_ALL)
    {
        $results = null;
        try {
            switch ($mode) {
                case 1:
                    $results = $statementResult->fetchAllAssociative();
                    break;
                case 2:
                    $results = $statementResult->fetchAssociative();
                    break;
                case 3:
                    $results = $statementResult->fetchFirstColumn();
                    break;
                case 4:
                    $results = $statementResult->fetchOne();
                    break;
            }
            return $results ?: null;
        } catch (DocException | DocDriverException $e) {
            return null;
        }
    }
}