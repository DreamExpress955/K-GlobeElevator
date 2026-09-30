<?php

/**
 * @file
 * @brief Provides database and CAN network functions for the K-Globe Elevator system.
 *
 * Contains functions for connecting to the elevator database, performing
 * CRUD operations on elevator and CAN log information, displaying database
 * tables, monitoring maintenance intervals, and controlling elevator
 * operating and door status information.
 */


/**
 * @brief Creates a connection to a database.
 *
 * Creates a PDO database connection using the supplied database path,
 * username, and password. The connection is configured to return
 * associative arrays and throw exceptions when database errors occur.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return PDO The configured database connection.
 */
function connect(string $path, string $user, string $password): PDO
{
    $db = new PDO($path, $user, $password);

    $db->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    return $db;
}


/**
 * @brief Inserts a new elevator log record into the CANLogs table.
 *
 * Creates a new record containing the date, time, elevator status,
 * current floor, requested floor, and additional information.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param string $current_date Date associated with the log entry.
 * @param string $current_time Time associated with the log entry.
 * @param int $status Elevator status value.
 * @param int $currentFloor Current elevator floor.
 * @param int $requestedFloor Requested elevator floor.
 * @param string $otherInfo Additional information about the elevator.
 *
 * @return void
 */
function insert(
    string $path,
    string $user,
    string $password,
    string $current_date,
    string $current_time,
    int $status,
    int $currentFloor,
    int $requestedFloor,
    string $otherInfo
): void {

    $db = connect($path, $user, $password);

    $query = "
        INSERT INTO CANLogs (
            Date,
            Time,
            Status,
            CurrentFloor,
            RequestedFloor,
            OtherInfo
        ) VALUES (
            :date,
            :time,
            :status,
            :currentFloor,
            :requestedFloor,
            :otherInfo
        )";

    $statement = $db->prepare($query);

    $statement->execute([
        'date' => $current_date,
        'time' => $current_time,
        'status' => $status,
        'currentFloor' => $currentFloor,
        'requestedFloor' => $requestedFloor,
        'otherInfo' => $otherInfo
    ]);
}


/**
 * @brief Displays the contents of the CANLogs table.
 *
 * Reads elevator and CAN information from the database and displays
 * the results as an HTML table.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param string $tablename Name of the table requested for display.
 *
 * @return void
 */
function showTable(
    string $path,
    string $user,
    string $password,
    string $tablename
): void
{
    $db = connect($path, $user, $password);

    $query = "
SELECT
    e.nodeID,
    e.Date,
    e.Time,
    e.Status,
    e.CurrentFloor,
    e.RequestedFloor,
    e.OtherInfo,
    c.canID,
    c.messageID,
    c.baudRate,
    c.lastMessage
FROM CANLogs e
LEFT JOIN CANLogs c
    ON e.nodeID = c.nodeID
";

    $statement = $db->query($query);

    $results = $statement->fetchAll();

    echo "<h4 class='mb-3'>Content of CANLogs Table</h4>";

    echo "<table class='table table-striped table-hover table-bordered'>";

    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th>Node ID</th>";
    echo "<th>Date</th>";
    echo "<th>Time</th>";
    echo "<th>Status</th>";
    echo "<th>Current Floor</th>";
    echo "<th>Requested Floor</th>";
    echo "<th>Other Info</th>";
    echo "<th>CAN ID</th>";
    echo "<th>Message ID</th>";
    echo "<th>Baud Rate</th>";
    echo "<th>Last Message</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";

    foreach ($results as $row)
    {
        echo "<tr>";

        echo "<td>" . htmlspecialchars($row['nodeID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Time']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CurrentFloor']) . "</td>";
        echo "<td>" . htmlspecialchars($row['RequestedFloor']) . "</td>";
        echo "<td>" . htmlspecialchars($row['OtherInfo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['canID'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['messageID'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['baudRate'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['lastMessage'] ?? '') . "</td>";

        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
}


/*
 * Original update function.
 *
 * This version has been left commented out because the active update()
 * function below uses transactions, validation, and exception handling.
 */

/*
// Update
function update(
    string $path,
    string $user,
    string $password,
    int $nodeID,
    int $newStatus,
    int $newCurrentFloor,
    int $newRequestedFloor,
    string $newOtherInfo
): void {

    $db = connect($path, $user, $password);

    $query = "
        UPDATE CANLogs
        SET
            Status = :status,
            CurrentFloor = :currentFloor,
            RequestedFloor = :requestedFloor,
            OtherInfo = :otherInfo
        WHERE nodeID = :nodeID";

    $statement = $db->prepare($query);

    $statement->execute([
        'status' => $newStatus,
        'currentFloor' => $newCurrentFloor,
        'requestedFloor' => $newRequestedFloor,
        'otherInfo' => $newOtherInfo,
        'nodeID' => $nodeID
    ]);
}
*/


/**
 * @brief Updates an elevator log using a database transaction.
 *
 * Validates the node ID and floor values before performing the update.
 * A database transaction is used so the operation can be rolled back
 * if an error occurs.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $nodeID Node ID of the record being updated.
 * @param int $newStatus New elevator status.
 * @param int $newCurrentFloor New current floor.
 * @param int $newRequestedFloor New requested floor.
 * @param string $newOtherInfo New additional information.
 *
 * @throws Exception If the node ID is invalid.
 * @throws Exception If either floor is outside the range 1 through 3.
 * @throws Exception If the specified node cannot be updated.
 * @throws Exception If the database transaction fails.
 *
 * @return void
 */
function update(
    string $path,
    string $user,
    string $password,
    int $nodeID,
    int $newStatus,
    int $newCurrentFloor,
    int $newRequestedFloor,
    string $newOtherInfo
): void {

    // Validate the node ID.
    if ($nodeID <= 0)
    {
        throw new Exception("Invalid Node ID.");
    }

    // Validate the current floor.
    if ($newCurrentFloor < 1 || $newCurrentFloor > 3)
    {
        throw new Exception(
            "Current floor must be between 1 and 3."
        );
    }

    // Validate the requested floor.
    if ($newRequestedFloor < 1 || $newRequestedFloor > 3)
    {
        throw new Exception(
            "Requested floor must be between 1 and 3."
        );
    }

    $db = connect($path, $user, $password);

    try
    {
        // Start the database transaction.
        $db->beginTransaction();

        $query = "
            UPDATE CANLogs
            SET
                Status = :status,
                CurrentFloor = :currentFloor,
                RequestedFloor = :requestedFloor,
                OtherInfo = :otherInfo
            WHERE nodeID = :nodeID
        ";

        $statement = $db->prepare($query);

        $statement->execute([
            'status' => $newStatus,
            'currentFloor' => $newCurrentFloor,
            'requestedFloor' => $newRequestedFloor,
            'otherInfo' => $newOtherInfo,
            'nodeID' => $nodeID
        ]);

        // Verify that a record was updated.
        if ($statement->rowCount() === 0)
        {
            throw new Exception(
                "Update failed. Node ID {$nodeID} was not found."
            );
        }

        // Commit the transaction if the update was successful.
        $db->commit();

        echo "Update successful.";
    }
    catch (Exception $e)
    {
        // Roll back the transaction when an error occurs.
        $db->rollBack();

        throw new Exception(
            "Transaction Failed: " . $e->getMessage()
        );
    }
}


/**
 * @brief Deletes an elevator log from the CANLogs table.
 *
 * Deletes the record associated with the specified node ID.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $nodeID Node ID of the record to delete.
 *
 * @return void
 */
function delete(
    string $path,
    string $user,
    string $password,
    int $nodeID
): void {

    $db = connect($path, $user, $password);

    $query = "
        DELETE FROM CANLogs
        WHERE nodeID = :nodeID
    ";

    $statement = $db->prepare($query);

    $statement->execute([
        'nodeID' => $nodeID
    ]);
}


/**
 * @brief Gets the most recent elevator floor from the database.
 *
 * Reads the currentFloor value from the elevatorNetwork entry with
 * the highest node ID.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return int Current elevator floor, or 0 when no record is found.
 */
function get_currentFloor(
    string $path,
    string $user,
    string $password
): int
{
    $db = connect($path, $user, $password);

    $query = "
        SELECT currentFloor
        FROM elevatorNetwork
        ORDER BY nodeID DESC
        LIMIT 1";

    $statement = $db->query($query);

    $result = $statement->fetch();

    return $result ? (int)$result['currentFloor'] : 0;
}


/**
 * @brief Inserts CAN information into the CANLogs table.
 *
 * Stores CAN node information including the node ID, message ID,
 * baud rate, and most recent message.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $nodeID CAN node ID.
 * @param int $messageID CAN message identifier.
 * @param int $baudRate CAN network baud rate.
 * @param string $lastMessage Most recent CAN message.
 *
 * @return void
 */
function insertCAN(
    string $path,
    string $user,
    string $password,
    int $nodeID,
    int $messageID,
    int $baudRate,
    string $lastMessage
): void {

    $db = connect($path, $user, $password);

    $query = "
    INSERT INTO CANLogs (
        nodeID,
        messageID,
        baudRate,
        lastMessage
    )
    VALUES (
        :nodeID,
        :messageID,
        :baudRate,
        :lastMessage
    )";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'nodeID' => $nodeID,
        'messageID' => $messageID,
        'baudRate' => $baudRate,
        'lastMessage' => $lastMessage
    ]);
}


/**
 * @brief Updates an existing CAN log entry.
 *
 * Updates the message ID, baud rate, and most recent message associated
 * with the specified CAN ID.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $canID CAN record ID to update.
 * @param int $messageID New CAN message identifier.
 * @param int $baudRate New CAN network baud rate.
 * @param string $lastMessage New last-message value.
 *
 * @return void
 */
function updateCAN(
    string $path,
    string $user,
    string $password,
    int $canID,
    int $messageID,
    int $baudRate,
    string $lastMessage
): void {

    $db = connect($path, $user, $password);

    $query = "
    UPDATE CANLogs
    SET
        messageID = :messageID,
        baudRate = :baudRate,
        lastMessage = :lastMessage
    WHERE canID = :canID
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'messageID' => $messageID,
        'baudRate' => $baudRate,
        'lastMessage' => $lastMessage,
        'canID' => $canID
    ]);
}


/**
 * @brief Deletes a CAN log entry.
 *
 * Removes a CANLogs record matching the supplied CAN ID.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $canID CAN record ID to delete.
 *
 * @return void
 */
function deleteCAN(
    string $path,
    string $user,
    string $password,
    int $canID
): void {

    $db = connect($path, $user, $password);

    $query = "
    DELETE FROM CANLogs
    WHERE canID = :canID";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'canID' => $canID
    ]);
}


/**
 * @brief Prepares a query for combined elevator and CAN information.
 *
 * Creates a query that combines elevator log information with CAN
 * information using the node ID.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return void
 */
function showCombinedTable(
    string $path,
    string $user,
    string $password
): void {

    $db = connect($path, $user, $password);

    $query = "
        SELECT
            e.nodeID,
            e.Date,
            e.Time,
            e.Status,
            e.OtherInfo,
            c.canID,
            c.messageID,
            c.baudRate,
            c.lastMessage
        FROM CANLogs e
        LEFT JOIN can c ON e.nodeID = c.nodeID
    ";
}


/**
 * @brief Displays recent CAN message logs.
 *
 * Retrieves the 50 most recent CANLogs records ordered by timestamp
 * and displays the information in an HTML table.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return void
 */
function showCANTable(
    string $path,
    string $user,
    string $password
): void
{
    $db = connect($path, $user, $password);

    $query = "
        SELECT *
        FROM CANLogs
        ORDER BY timestamp DESC
        LIMIT 50
    ";

    $statement = $db->query($query);

    $results = $statement->fetchAll();

    echo "<h5>CAN Message Log</h5>";

    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>Log ID</th>";
    echo "<th>Timestamp</th>";
    echo "<th>Node ID</th>";
    echo "<th>Message ID</th>";
    echo "<th>Length</th>";
    echo "<th>Data</th>";
    echo "<th>Description</th>";
    echo "</tr>";

    foreach ($results as $row)
    {
        echo "<tr>";

        echo "<td>" . htmlspecialchars($row['logID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nodeID']) . "</td>";
        echo "<td>0x" . strtoupper(dechex($row['messageID'])) . "</td>";
        echo "<td>" . htmlspecialchars($row['dataLength']) . "</td>";
        echo "<td>" . htmlspecialchars($row['messageData']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";

        echo "</tr>";
    }

    echo "</table>";
}


/**
 * @brief Gets the number of records stored in CANLogs.
 *
 * Counts all records currently contained in the CANLogs table.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return int Number of records in the CANLogs table.
 */
function getLogCount(
    string $path,
    string $user,
    string $password
): int {

    $db = connect($path, $user, $password);

    return (int)$db
        ->query("SELECT COUNT(*) FROM CANLogs")
        ->fetchColumn();
}


/**
 * @brief Determines the elevator maintenance status.
 *
 * Uses the number of database log records to determine whether the
 * elevator is operating normally, requires inspection, is approaching
 * a maintenance interval, or should enter maintenance mode.
 *
 * Inspection is activated at 325 records, a warning is activated at
 * 350 records, and maintenance mode is activated at 5000 records.
 *
 * @param int $recordCount Number of CAN log records.
 *
 * @return array Maintenance status flags and status message.
 */
function getMaintenanceStatus(int $recordCount): array
{
    $status = [
        'inspection' => false,
        'warning' => false,
        'maintenance' => false,
        'message' => 'Normal Operation'
    ];

    if ($recordCount >= 5000)
    {
        $status['maintenance'] = true;

        $status['message'] =
            "Maintenance Mode Activated Automatically";
    }
    elseif ($recordCount >= 350)
    {
        $status['warning'] = true;

        $status['message'] =
            "WARNING: Elevator approaching maintenance interval";
    }
    elseif ($recordCount >= 325)
    {
        $status['inspection'] = true;

        $status['message'] =
            "Inspection Required";
    }

    return $status;
}


/**
 * @brief Gets the current elevator operating mode.
 *
 * Reads the stopFlag value from the most recent elevatorNetwork record.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return int Current stopFlag value, or 0 when no record is found.
 */
function getMode(
    string $path,
    string $user,
    string $password
): int {

    $db = connect($path, $user, $password);

    $query = "
        SELECT stopFlag
        FROM elevatorNetwork
        ORDER BY nodeID DESC
        LIMIT 1
    ";

    $stmt = $db->query($query);

    $result = $stmt->fetch();

    return $result ? (int)$result['stopFlag'] : 0;
}


/**
 * @brief Sets the elevator operating mode.
 *
 * Updates the stopFlag field for the most recent node in the
 * elevatorNetwork table.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 * @param int $mode New elevator operating mode.
 *
 * @return void
 */
function setMode(
    string $path,
    string $user,
    string $password,
    int $mode
): void {

    $db = connect($path, $user, $password);

    $query = "
        UPDATE elevatorNetwork
        SET stopFlag = :mode
        WHERE nodeID = (
            SELECT nodeID
            FROM (
                SELECT nodeID
                FROM elevatorNetwork
                ORDER BY nodeID DESC
                LIMIT 1
            ) latest
        )
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'mode' => $mode
    ]);
}


/**
 * @brief Gets the current elevator door status.
 *
 * Retrieves the doorOpen value from the most recent elevatorNetwork
 * record.
 *
 * @param string $path Database connection string.
 * @param string $user Database username.
 * @param string $password Database password.
 *
 * @return int Current doorOpen value, or 0 when no record is found.
 */
function getDoorStatus(
    string $path,
    string $user,
    string $password
): int {

    $db = connect($path, $user, $password);

    $query = "
        SELECT doorOpen
        FROM elevatorNetwork
        ORDER BY nodeID DESC
        LIMIT 1
    ";

    $stmt = $db->query($query);

    $result = $stmt->fetch();

    return $result ? (int)$result['doorOpen'] : 0;
}

?>