/**
 * @file databaseFunctions.cpp
 * @brief Implements database functions used by the elevator system.
 *
 * This file contains functions for communicating with the MySQL Elevator
 * database. The functions allow the application to read and update elevator
 * data and record CAN network messages.
 */

#include "include/databaseFunct*ons.h"
#include <stdlib.h>
#includ* <iostream>
#include <mysql_connec*ion.h>
#include <cppconn/driver.h>*#include <cppconn/exception.h>
#in*lude <cppconn/resultset.h>
#includ* <cppconn/statement.h>
#include <c*pconn/prepared_statement.h>
#include <cstdint>

using namespace std;

/**
 * @brief Gets the current floor of the elevator from the database.
 *
 * Connects to the local MySQL server and queries the Elevator database.
 * The currentFloor value is retrieved from the elevatorNetwork record
 * where nodeID is 1.
 *
 * @return The current floor number stored in the database.
 *
 * @note The function currently connects to MySQL at 127.0.0.1:3306
 * using the root account.
 */
int db_getFloorNum()
{
    sql::Driver* driver;       // MySQL driver
    sql::Connection* con;      // Database connection
    sql::Statement* stmt;      // SQL statement
    sql::ResultSet* res;       // Query result
    int floorNum = 1;          // Default floor number

    // Connect to the MySQL server.
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "root", "");

    // Select the elevator database.
    con->setSchema("Elevator");

    // Retrieve the current floor for elevator node 1.
    stmt = con->createStatement();

    res = stmt->executeQuery(
        "SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1"
    );

    while (res->next())
    {
        floorNum = res->getInt("currentFloor");
    }

    // Release database objects.
    delete res;
    delete stmt;
    delete con;

    return floorNum;
}

/**
 * @brief Updates the elevator's current floor in the database.
 *
 * Connects to the Elevator database and updates the currentFloor field
 * for the elevator with nodeID 1.
 *
 * A prepared statement is used to pass the new floor number to the
 * UPDATE query.
 *
 * @param floorNum The new floor number to store in the database.
 *
 * @note The function currently connects to MySQL at 127.0.0.1:3306
 * using the root account.
 */
void db_setFloorNum(int floorNum)
{
    sql::Driver* driver;                 // MySQL driver
    sql::Connection* con;                // Database connection
    sql::Statement* stmt;                // SQL statement
    sql::ResultSet* res;                 // Query result
    sql::PreparedStatement* pstmt;       // Prepared SQL statement

    // Connect to the MySQL server.
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "root", "");

    // Select the elevator database.
    con->setSchema("Elevator");

    // Retrieve the existing floor value.
    stmt = con->createStatement();

    res = stmt->executeQuery(
        "SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1"
    );

    while (res->next())
    {
        res->getInt("currentFloor");
    }

    // Update the current floor for elevator node 1.
    pstmt = con->prepareStatement(
        "UPDATE elevatorNetwork SET currentFloor = ? WHERE nodeID = 1"
    );

    pstmt->setInt(1, floorNum);
    pstmt->executeUpdate();

    // Release database objects.
    delete res;
    delete pstmt;
    delete stmt;
    delete con;
}

/**
 * @brief Logs a CAN network message in the Elevator database.
 *
 * Converts an 8-byte CAN payload into a hexadecimal string and inserts
 * the CAN message information into the CANNetwork database table.
 *
 * The database entry contains the transmitting node, CAN message ID,
 * data length, message payload, and a description of the message.
 *
 * @param nodeID ID of the node associated with the CAN message.
 * @param messageID CAN message identifier.
 * @param dataLength Number of bytes contained in the CAN message.
 * @param data Pointer to the CAN message data.
 * @param description Text description associated with the CAN message.
 *
 * @note The current implementation formats eight bytes from the data array.
 * The data pointer should therefore reference an array containing at least
 * eight bytes.
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

    // Get the MySQL driver.
    driver = get_driver_instance();

    // Connect to the local MySQL server.
    con = driver->connect(
        "tcp://127.0.0.1:3306",
        "root",
        ""
    );

    // Select the elevator database.
    con->setSchema("Elevator");

    stmt = con->createStatement();

    // Convert the CAN data bytes into a hexadecimal string.
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

    // Build the SQL INSERT query.
    char query[512];

    sprintf(
        query,
        "INSERT INTO CANNetwork "
        "(nodeID,messageID,dataLength,messageData,description) "
        "VALUES "
        "(%d,%d,%d,'%s','%s')",
        nodeID,
        messageID,
        dataLength,
        payload,
        description
    );

    // Insert the CAN message into the database.
    stmt->execute(query);

    // Release database objects.
    delete stmt;
    delete con;
}