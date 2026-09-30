<?php

/**
 * @file
 * @brief Defines the Database class used to connect to the elevator database.
 *
 * Provides a centralized location for storing the database connection
 * information and creating a connection to the MySQL elevator database.
 */

/**
 * @class Database
 * @brief Provides a connection to the elevator MySQL database.
 *
 * The Database class stores the database connection information as
 * private static properties. The connect() method uses these properties
 * to create and return a MySQL database connection.
 */
class Database
{
    /**
     * @var string
     * @brief Hostname of the MySQL database server.
     */
    private static $host = "localhost";

    /**
     * @var string
     * @brief Username used to connect to the MySQL database.
     */
    private static $username = "root";

    /**
     * @var string
     * @brief Password used to connect to the MySQL database.
     */
    private static $password = "";

    /**
     * @var string
     * @brief Name of the elevator database.
     */
    private static $dbname = "elevator_db";

    /**
     * @brief Creates a connection to the elevator database.
     *
     * Uses the stored host, username, password, and database name
     * to create a new MySQL connection.
     *
     * @return mysqli The MySQL database connection object.
     */
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