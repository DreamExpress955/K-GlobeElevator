// Includes required (headers located in /usr/include) 
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
 
static sql::Connection* db_openConnection()
{
    sql::Driver *driver;
    sql::Connection *con;

    driver = get_driver_instance();

    con = driver->connect(
        "tcp://127.0.0.1:3306",
        "myphpadmin",
        "ese1"
    );

    con->setSchema("Elevator");

    return con;
}

void db_updateDoor(int door){
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

int db_getFloorNum() {
	sql::Driver *driver; 			// Create a pointer to a MySQL driver object
	sql::Connection *con; 			// Create a pointer to a database connection object
	sql::Statement *stmt;			// Crealte a pointer to a Statement object to hold statements 
	sql::ResultSet *res;			// Create a pointer to a ResultSet object to hold results 
	int floorNum;					// Floor number 
	
	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("host=127.0.0.1", "phpmyadmin", "ese1");	
	con->setSchema("Elevator");		
	
	// Query database
	// ***************************** 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT currentFloor FROM elevatorNetwork");	// message query
	while(res->next()){
		floorNum = res->getInt("currentFloor");
	}
	
	// Clean up pointers 
	delete res;
	delete stmt;
	delete con;
	
	return floorNum;
}
 
 
int db_setFloorNum(int floorNum) 
	{
    sql::Driver *driver;
    sql::Connection *con = NULL;
    sql::PreparedStatement *pstmt = NULL;

    try
    {
        // Create connection
        driver = get_driver_instance();
        con = driver->connect(
            "host=127.0.0.1",
            "myphpadmin",
            "ese1"
        );

        con->setSchema("Elevator");

        // Update current floor for node 1
        pstmt = con->prepareStatement(
            "UPDATE elevatorNetwork "
            "SET currentFloor = ? "
            "WHERE nodeID = 1"
        );

        pstmt->setInt(1, floorNum);

        int rowsUpdated = pstmt->executeUpdate();

        cout << "Setting floor to: "
             << floorNum
             << endl;

        cout << "Rows updated: "
             << rowsUpdated
             << endl;
    }
    catch (sql::SQLException &error)
    {
        cerr << "db_setFloorNum error: "
             << error.what()
             << endl;

        delete pstmt;
        delete con;

        return -1;
    }

    delete pstmt;
    delete con;

    return 0;
}
 
void db_logCANMessage(int nodeID, int messageID, int dataLength, uint8_t* data,
    const char* description)
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
	//printf("1\n");
    con->setSchema("Elevator");
	//printf("2\n");
    stmt = con->createStatement();

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
	//printf("3\n");
    char query[512];
	//printf("Logging CAN message to database: nodeID=%d, messageID=0x%04x, dataLength=%d, payload=%s, description=%s\n",
	//	nodeID,
	//	messageID,
	//	dataLength,
	//	payload,
	//	description
	//);
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

    stmt->execute(query);

    delete stmt;
    delete con;
	return;
}

int db_getRequestedFloor()
{
    sql::Connection *con = NULL;
    sql::Statement *stmt = NULL;
    sql::ResultSet *res = NULL;

    int requestedFloor = 0;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Query database
        // *****************************
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT requestedFloor "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            requestedFloor = res->getInt("requestedFloor");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getRequestedFloor error: "
             << error.what()
             << endl;
    }

    // Clean up pointers
    delete res;
    delete stmt;
    delete con;

    return requestedFloor;
}

int db_getRequestType()
{
    sql::Connection *con = NULL;
    sql::Statement *stmt = NULL;
    sql::ResultSet *res = NULL;

    int requestType = 0;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Query database
        // *****************************
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT requestedType "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            requestType = res->getInt("requestedType");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getRequestType error: "
             << error.what()
             << endl;
    }

    // Clean up pointers
    delete res;
    delete stmt;
    delete con;

    return requestType;
}

int db_clearWebsiteRequest()
{
    sql::Connection *con = NULL;
    sql::PreparedStatement *pstmt = NULL;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Clear request
        // *****************************
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

    // Clean up pointers
    delete pstmt;
    delete con;

    return 0;
}

int db_getStopFlag()
{
    sql::Connection *con = NULL;
    sql::Statement *stmt = NULL;
    sql::ResultSet *res = NULL;

    int stopFlag = 0;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Query database
        // *****************************
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT stopFlag "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            stopFlag = res->getInt("stopFlag");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getStopFlag error: "
             << error.what()
             << endl;
    }

    // Clean up pointers
    delete res;
    delete stmt;
    delete con;

    return stopFlag;
}

