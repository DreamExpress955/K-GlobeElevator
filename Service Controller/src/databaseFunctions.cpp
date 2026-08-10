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
 
int db_getFloorNum() {
	sql::Driver *driver; 			// Create a pointer to a MySQL driver object
	sql::Connection *con; 			// Create a pointer to a database connection object
	sql::Statement *stmt;			// Crealte a pointer to a Statement object to hold statements 
	sql::ResultSet *res;			// Create a pointer to a ResultSet object to hold results 
	int floorNum;					// Floor number 
	
	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("host=127.0.0.1", "phpmyadmin", "ese1");	
	con->setSchema("elevator");		
	
	// Query database
	// ***************************** 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1");	// message query
	while(res->next()){
		floorNum = res->getInt("currentFloor");
	}
	
	// Clean up pointers 
	delete res;
	delete stmt;
	delete con;
	
	return floorNum;
}
 
 
int db_setFloorNum(int floorNum) {
	sql::Driver *driver; 				// Create a pointer to a MySQL driver object
	sql::Connection *con; 				// Create a pointer to a database connection object
	sql::Statement *stmt;				// Create a pointer to a Statement object to hold statements 
	sql::ResultSet *res;				// Create a pointer to a ResultSet object to hold results 
	sql::PreparedStatement *pstmt; 		// Create a pointer to a prepared statement	
	
	// Create a connection 
	driver = get_driver_instance();
	con = driver->connect("host=127.0.0.1", "myphpadmin", "ese1");	
	con->setSchema("elevator");										
	
	// Query database (possibly not necessary)
	// ***************************** 
	stmt = con->createStatement();
	res = stmt->executeQuery("SELECT currentFloor FROM elevatorNetwork WHERE nodeID = 1");	// message query
	while(res->next()){
		res->getInt("currentFloor");
	}
		
	// Update database
	// *****************************
	pstmt = con->prepareStatement("UPDATE elevatorNetwork SET currentFloor = ? WHERE nodeID = 1");
	pstmt->setInt(1, floorNum);
	pstmt->executeUpdate();
		
	// Clean up pointers 
	delete res;
	delete pstmt;
	delete stmt;
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