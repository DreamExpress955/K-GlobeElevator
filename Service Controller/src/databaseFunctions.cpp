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
 //connect to the data base
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

    con->setSchema("elevator");

    return con;
}

int db_getFloorNum()
{
    sql::Connection *con = NULL;
    sql::Statement *stmt = NULL;
    sql::ResultSet *res = NULL;

    int floorNum = 0;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Query database
        // *****************************
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT currentFloor "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            floorNum = res->getInt("currentFloor");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getFloorNum error: "
             << error.what()
             << endl;
    }

    // Clean up pointers
    delete res;
    delete stmt;
    delete con;

    return floorNum;
}
 
int db_setFloorNum(int floorNum)
{
    sql::Connection *con = NULL;
    sql::PreparedStatement *pstmt = NULL;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Update database
        // *****************************
        pstmt = con->prepareStatement(
            "UPDATE elevatorNetwork "
            "SET currentFloor = ? "
            "WHERE nodeID = 1"
        );

        pstmt->setInt(1, floorNum);
        pstmt->executeUpdate();
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

    // Clean up pointers
    delete pstmt;
    delete con;

    return 0;
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
            "SELECT requestType "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            requestType = res->getInt("requestType");
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
            "requestType = 0 "
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
int db_getSequenceFlag()
{
    sql::Connection *con = NULL;
    sql::Statement *stmt = NULL;
    sql::ResultSet *res = NULL;

    int sequenceFlag = 0;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Query database
        // *****************************
        stmt = con->createStatement();

        res = stmt->executeQuery(
            "SELECT sequenceFlag "
            "FROM elevatorNetwork "
            "WHERE nodeID = 1"
        );

        while (res->next())
        {
            sequenceFlag = res->getInt("sequenceFlag");
        }
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_getSequenceFlag error: "
             << error.what()
             << endl;
    }

    // Clean up pointers
    delete res;
    delete stmt;
    delete con;

    return sequenceFlag;
}
int db_clearSequenceFlag()
{
    sql::Connection *con = NULL;
    sql::PreparedStatement *pstmt = NULL;

    try
    {
        // Create a connection
        con = db_openConnection();

        // Clear sequence flag
        // *****************************
        pstmt = con->prepareStatement(
            "UPDATE elevatorNetwork "
            "SET sequenceFlag = 0 "
            "WHERE nodeID = 1"
        );

        pstmt->executeUpdate();
    }
    catch (sql::SQLException& error)
    {
        cerr << "db_clearSequenceFlag error: "
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