/**
 * @file Connector_C++.cpp
 * @brief Tests the MySQL Connector/C++ connection and elevator database functions.
 *
 * This program tests communication between the elevator application and the
 * MySQL Elevator database. It uses the functions provided by
 * databaseFunctions.h to read and update elevator information and to log
 * CAN messages.
 *
 * @note This project uses MySQL Connector/C++ and Boost 1.85.
 * @note Apache and MySQL must be running before database operations are tested.
 */

#include <iostream>
#include <stdlib.h>
#include "mysql_connection.h"
#include "mysql_driver.h"

#include <cppconn/driver.h>
#include <cppconn/build_config.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>
#include <cstdint>

#include "include/databaseFunctions.h"

using namespace std;

/**
 * @brief Runs tests for the elevator database functions.
 *
 * The program tests communication with the Elevator database by:
 * - Reading the current floor from the database.
 * - Updating the current floor.
 * - Reading the floor again to verify the update.
 * - Logging a sample CAN message to the CANNetwork table.
 *
 * @return 0 if the test program completes successfully.
 */
int main(void)
{
    /*
     * Part 1:
     * Original MySQL Connector/C++ connection test.
     *
     * This section has been left commented out for reference.
     */

    /*
    cout << "Running 'Select 'Hello World' AS _message' ..." << endl;

    sql::Driver* driver;
    sql::Connection* con;
    sql::Statement* stmt;
    sql::ResultSet* res;

    // Create a connection to the local MySQL server.
    driver = get_driver_instance();
    con = driver->connect("tcp://127.0.0.1:3306", "root", "");
    con->setSchema("test");

    // Execute a test query.
    stmt = con->createStatement();
    res = stmt->executeQuery("SELECT 'Hello World!' AS _message");

    while (res->next())
    {
        cout << "\t.. MySQL replies:: ";
        cout << res->getString("_message") << endl;
    }

    int floorNum;

    floorNum = db_getFloorNum();
    cout << "Before: " << floorNum << endl;

    db_setFloorNum(76);

    floorNum = db_getFloorNum();
    cout << "After: " << floorNum << endl;

    // Clean up database objects.
    delete res;
    delete stmt;
    delete con;

    return 0;
    */

    // Part 2:
    // Test the elevator database functions.
    cout << "Testing database connection..." << endl;

    // Read the current elevator floor.
    int floorNum = db_getFloorNum();

    cout << "Current floor: " << floorNum << endl;

    // Change the floor number and verify the change.
    db_setFloorNum(76);

    cout << "New floor: " << db_getFloorNum() << endl;

    // Create sample CAN data for testing database logging.
    std::uint8_t testData[8] =
    {
        0x01,
        0x02,
        0x03,
        0x04,
        0x05,
        0x06,
        0x07,
        0x08
    };

    // Log a sample CAN message.
    db_logCANMessage(
        1,                  // Node ID
        0x123,              // CAN message ID
        8,                  // Number of data bytes
        testData,           // CAN payload
        "Manual CAN Test"   // Log description
    );

    cout << "CAN message logged successfully." << endl;

    return 0;
}