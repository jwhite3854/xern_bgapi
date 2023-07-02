<?php

namespace Helium\traits;

use Doctrine\DBAL\Exception as DocException;
use Doctrine\DBAL\Statement;
use Helium\services\Connection;

trait ConnectableTrait
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @param array $options
     */
    protected function setupConnection(array $options = [])
    {
        try {
            $this->conn = new Connection($options);
        } catch (DocException $e) {

        }
    }

    /**
     * @param string $sql
     *
     * @return Statement
     */
    public function prepareConn(string $sql): ?Statement
    {
        try {
            $statement = $this->conn->getConn()->prepare($sql);
        } catch (DocException $e) {
            return null;
        }

        return $statement;
    }
}