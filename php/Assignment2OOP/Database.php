<?php

class Database
{
    private static $host = "localhost";
    private static $username = "root";
    private static $password = "";
    private static $dbname = "elevator_db";

    public static function connect()
    {
        $conn = new mysqli(
            self::$host,
            self::$username,
            self::$password,
            self::$dbname
        );

        return $conn;
    }
}

?>