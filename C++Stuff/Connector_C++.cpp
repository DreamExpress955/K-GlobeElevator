/* Connector/C++ Test example 
 * Project uses Connector C++ and Boost 1.85
 * Make sure Apache and MySQL are runnning
 */

#include <iostream>
#include <stdlib.h>
#include "mysql_connection.h"
#include "mysql_driver.h"

#include <cppconn/driver.h>
#include <cppconn/build_config.h>  
#include <cppconn/resultset.h>
#include <cppconn/statement.h>


#include "include/databaseFunctions.h"		// Functions used to connect to and edit the Elevator Database in the second part of this exercise

using namespace std;

int main(void) {

	// Part 1: Test connection and functionality of Connector C++

	// /*

	cout << "Running 'Select 'Hello World' AS _message' ..." << endl;

	sql::Driver* driver;	// Pointer to MySQL driver object
	sql::Connection* con;	// Pointer to database connection object
	sql::Statement* stmt;	// Pointer to statement object
	sql::ResultSet* res;	// Pointer to ResultSet object

	// Create a connection
	driver = get_driver_instance();
	con = driver->connect("tcp://127.0.0.1:3306", "root", ""); // IP and password of MySQL server database  - Note that "root" and "" (no password) should work - make sure Apache and MySQL are running
	con->setSchema("test");	// Connect to the MySQL "test" database - replace with your database

	// Execute a query and wait for result 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT 'Hello World!' AS _message");  // Query (see previous lectures)
	while (res->next()) {
		cout << "\t.. MySQL replies:: ";
		cout << res->getString("_message") << endl;
	}

	int floorNum;

	floorNum = db_getFloorNum();
	cout << "Before: " << floorNum << endl;

	db_setFloorNum(76);

	floorNum = db_getFloorNum();
	cout << "After: " << floorNum << endl;

	// Clean up pointers 
	delete res;
	delete stmt;
	delete con;
	return 0;

	// */

	// Part 2: Use the databaseFunctions to connect to and edit the Elevator Database on the RPi (a remote database) from this program 

}