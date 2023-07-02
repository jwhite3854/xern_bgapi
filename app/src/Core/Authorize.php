<?php

namespace Helium\Core;

use Helium\Helium;
use PDO;

class Authorize
{

    private $pdo;

    public function __construct(array $configs = [])
    {
        $this->pdo = new PDO("mysql:host=mysql_8;dbname=xern_battleground", 'root', 'root');
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    /**
     * @return false|string
     */
    public function getUser()
    {
        if ($authToken = Helium::getAuthToken()) {
            $stmt = $this->pdo->prepare("SELECT * FROM player_tokens WHERE access_token = :access_token");
            $stmt->bindParam('access_token', $authToken);
            $stmt->execute();

            $unbufferedResult = $stmt->fetchColumn();
            foreach ($unbufferedResult as $row) {
                echo $row['Name'] . PHP_EOL;

                return '';
            }
        }

        return false;
    }

}