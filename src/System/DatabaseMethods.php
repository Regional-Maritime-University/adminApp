<?php

namespace Src\System;

use Src\System\DatabaseConnector;

class DatabaseMethods
{
    private $conn;

    function __construct()
    {
        $this->conn = (new DatabaseConnector())->getConnection();
    }

    private function query($str, $params = array())
    {
        $stmt = $this->conn->prepare($str);
        $stmt->execute($params);
        if (explode(' ', $str)[0] == 'SELECT' || explode(' ', $str)[0] == 'CALL') {
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } elseif (explode(' ', $str)[0] == 'INSERT' || explode(' ', $str)[0] == 'UPDATE' || explode(' ', $str)[0] == 'DELETE') {
            return 1;
        }
    }

    //Get raw data from db
    final public function getID($str, $params = array())
    {
        try {
            $result = $this->query($str, $params);
            if (!empty($result))  return $result[0]["id"];
            return 0;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    //Get raw data from db
    final public function getData($str, $params = array())
    {
        try {
            $result = $this->query($str, $params);
            if (!empty($result)) return $result;
            return 0;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    //Insert, Upadate or Delete Data
    final public function inputData($str, $params = array())
    {
        try {
            $result = $this->query($str, $params);
            if (!empty($result)) return $result;
            return 0;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
