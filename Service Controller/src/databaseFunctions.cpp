/**
 * @file databaseFunctions.cpp
 * @brief Implements database functions used by the elevator Service Controller.
 *
 * Provides the database functionality required by the K-Globe Elevator
 * Service Controller. These functions communicate with the MySQL Elevator
 * database to read and update elevator information, process website floor
 * requests, log CAN messages, update the elevator door status, and retrieve
 * the system stop flag.
 */

// Includes required for database communication.
#include "../include/databaseFunctions.h"
#include <stdlib.h>
#include <iostream>
#include <mysql_connection.h>
#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cppconn/prepared_statement.h>

using namespace std;


/**
 * @brief Opens a connection to the Elevator database.
 *
 * Creates a MySQL database connection and selects the Elevator schema.
 * This helper function is used by several of the Service Controller
 * database functions.
 *
 * @return Pointer to the open MySQL database connection.
 */
static sql::Connection* db_openConnection()
{
    sql::Driver* driver;
    sql::Connection* con;

    driver = get_driver_instance();

    con = driver->connect(
        "tcp://127.0.0.1:3306",
        "myphpadmin",
        "ese1"
    );

    con->setSchema("Elevator");

    return con;
}


/**
 * @brief Updates the elevator door status in the database.
 *
 * Updates the doorOpen field for node 1 in the elevatorNetwork table.
 *
 * @param door Door status value to store in the database.
 *
 * @return void
 */
void db_updateDoor(int door)
{
    sql::Driver* driver;
    sql::Connection* con;
    sql::PreparedStatement* pstmt;

    driver = get_driver_instance();

    con = driver->connect(
        "host=127.0.0.1",
        "myphpadmin",
        "ese1"
    );

    con->setSchema("Elevator");

    pstmt = con->prepareStatement(
        "UPDATE elevatorNetwork "
        "SET doorOpen = ? "
        "WHERE nodeID = 1"
    );

    pstmt->setInt(1, door);

    pstmt->executeUpdate();

    delete pstmt;
    delete con;

    return;
}


/**
 * @brief Gets the elevator's current floor from the database.
 *
 * Connects to the Elevator database and reads the currentFloor
 * value from the elevatorNetwork table.
 *
 * @return The current elevator floor stored in the database.
 */
int db_getFloorNum()
{
    sql::Driver* driver;
    sql::Connection* con;
    sql::Statement* stmt;
    sql::ResultSet* res;

    int floorNum;

    // Create a database connection.
    driver = get_driver_instance();

    con = driver->connect(
        "host=127.0.0.1",
        "phpmyadmin",
        "ese1"
    );

    con->setSchema("Elevator");

    // Query the current elevator floor.
    stmt = con->createStatement();

    res = stmt->executeQuery(
        "SELECT currentFloor FROM elevatorNetwork"
    );

    while (res->next())
    {
        floorNum = res->getInt("currentFloor");
    }

    // Clean up database objects.
    delete res;
    delete stmt;
    delete con;

    return floorNum;
}


/**
 * @brief Sets the elevator's current floor in the database.
 *
 * Updates the currentFloor field for node 1 in the elevatorNetwork
 * table using a prepared SQL statement.
 *
 * The number of affected database rows is printed to the console for
 * debugging purposes.
 *
 * @param floorNum New current floor to store in the database.
 *
 * @return 0 if the database update completes successfully.
 * @return -1 if a MySQL exception occurs.
 */
int db_setFloorNum(int floorNum)
{
    sql::Driver* driver;
    sql::Connection* con = NULL;
    sql::PreparedStatement* pstmt = NULL;

    try
    {
        // Create database connection.
        driver = get_driver_instance();

        con = driver->connect(
            "host=127.0.0.1",
            "myphpadmin",
            "ese1"
        );

        con->setSchema("Elevator");

        // Update the current floor for node 1.
        pstmt = con->prepareStatement(
            "UPDATE elevatorNetwork "
            "SET currentFloor = ? "
            "WHERE nodeID = 1"
        );

        pstmt->setInt(
            1,
            floorNum
        );

        int rowsUpdated = pstmt->executeUpdate();

        cout << "Setting floor to: "
             << floorNum
             << endl;

        cout << "Rows updated: "
             << rowsUpdated
             << endl;
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_setFloorNum error: "
             << error.what()
             << endl;

        delete pstmt;
        delete con;

        return -1;
    }

    // Clean up database objects.
    delete pstmt;
    delete con;

    return 0;
}


/**
 * @brief Logs a CAN message to the Elevator database.
 *
 * Converts the CAN data payload into a hexadecimal string and stores
 * the CAN message information in the CANLogs table.
 *
 * The database entry includes the node ID, CAN message ID, data length,
 * hexadecimal payload, and a description of the message.
 *
 * @param nodeID ID of the CAN node associated with the message.
 * @param messageID CAN message identifier.
 * @param dataLength Number of bytes in the CAN message.
 * @param data Pointer to the CAN message data.
 * @param description Description associated with the CAN message.
 *
 * @return void
 *
 * @note The current implementation formats eight bytes from the data
 * array regardless of the value of dataLength.
 */
void db_logCANMessage(
    int nodeID,
    int messageID,
    int dataLength,
    uint8_t* data,
    const char* description
)
{
    sql::Driver* driver;
    sql::Connection* con;
    sql::Statement* stmt;

    driver = get_driver_instance();

    con = driver->connect(
        "host=127.0.0.1",
        "myphpadmin",
        "ese1"
    );

    con->setSchema("Elevator");

    stmt = con->createStatement();

    // Convert the CAN payload into a hexadecimal string.
    char payload[50];

    sprintf(
        payload,
        "%02X %02X %02X %02X %02X %02X %02X %02X",
        data[0],
        data[1],
        data[2],
        data[3],
        data[4],
        data[5],
        data[6],
        data[7]
    );

    char query[512];

    /*
     * Build the SQL query used to insert the CAN message
     * into the CANLogs table.
     */
    sprintf(
        query,
        "INSERT INTO CANLogs "
        "(nodeID,messageID,dataLength,messageData,description) "
        "VALUES "
        "(%d,%d,%d,'%s','%s')",
        nodeID,
        messageID,
        dataLength,
        payload,
        description
    );

    // Execute the CAN log query.
    stmt->execute(query);

    // Clean up database objects.
    delete stmt;
    delete con;

    return;
}


/**
 * @brief Gets the floor requested through the website.
 *
 * Reads the requestedFloor value from node 1 in the elevatorNetwork
 * table.
 *
 * @return The requested floor number.
 *
 * @note Returns 0 if no floor value is retrieved or if a database
 * exception occurs.
 */
int db_getRequestedFloor()
{
    sql::Connection* con = NULL;
    sql::Statement* stmt = NULL;
    sql::ResultSet* res = NULL;

    int requestedFloor = 0;

    try
    {
        // Create a database connection.
        con = db_openConnection();

        // Query the requested floor.
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT requestedFloor "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            requestedFloor =
                res->getInt("requestedFloor");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getRequestedFloor error: "
             << error.what()
             << endl;
    }

    // Clean up database objects.
    delete res;
    delete stmt;
    delete con;

    return requestedFloor;
}


/**
 * @brief Gets the current website request type.
 *
 * Reads the requestedType value for node 1 from the elevatorNetwork
 * table. The request type identifies the source or type of the
 * elevator request.
 *
 * @return The request type stored in the database.
 *
 * @note Returns 0 if no request type is retrieved or if a database
 * exception occurs.
 */
int db_getRequestType()
{
    sql::Connection* con = NULL;
    sql::Statement* stmt = NULL;
    sql::ResultSet* res = NULL;

    int requestType = 0;

    try
    {
        // Create a database connection.
        con = db_openConnection();

        // Query the website request type.
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT requestedType "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            requestType =
                res->getInt("requestedType");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getRequestType error: "
             << error.what()
             << endl;
    }

    // Clean up database objects.
    delete res;
    delete stmt;
    delete con;

    return requestType;
}


/**
 * @brief Clears the current website elevator request.
 *
 * Resets both requestedFloor and requestedType to 0 for node 1
 * in the elevatorNetwork table after the website request has
 * been processed.
 *
 * @return 0 if the request is cleared successfully.
 * @return -1 if a MySQL exception occurs.
 */
int db_clearWebsiteRequest()
{
    sql::Connection* con = NULL;
    sql::PreparedStatement* pstmt = NULL;

    try
    {
        // Create a database connection.
        con = db_openConnection();

        // Clear the website request.
        pstmt = con->prepareStatement(
            "UPDATE elevatorNetwork "
            "SET requestedFloor = 0, "
            "requestedType = 0 "
            "WHERE nodeID = 1"
        );

        pstmt->executeUpdate();
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_clearWebsiteRequest error: "
             << error.what()
             << endl;

        delete pstmt;
        delete con;

        return -1;
    }

    // Clean up database objects.
    delete pstmt;
    delete con;

    return 0;
}


/**
 * @brief Gets the current elevator stop flag.
 *
 * Reads the stopFlag value for node 1 from the elevatorNetwork table.
 * The Service Controller can use this value to determine the requested
 * operating state of the elevator system.
 *
 * @return The current stopFlag value stored in the database.
 *
 * @note Returns 0 if no stop flag is retrieved or if a database
 * exception occurs.
 */
int db_getStopFlag()
{
    sql::Connection* con = NULL;
    sql::Statement* stmt = NULL;
    sql::ResultSet* res = NULL;

    int stopFlag = 0;

    try
    {
        // Create a database connection.
        con = db_openConnection();

        // Query the current stop flag.
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT stopFlag "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            stopFlag =
                res->getInt("stopFlag");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getStopFlag error: "
             << error.what()
             << endl;
    }

    // Clean up database objects.
    delete res;
    delete stmt;
    delete con;

    return stopFlag;
}