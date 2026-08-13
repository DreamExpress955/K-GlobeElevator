<?php 
// CRUD (Create, Read, Update, Delete) functions
function connect(string $path, string $user, string $password): PDO
{
    $db = new PDO($path, $user, $password);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
}
// Create
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
// Read
function showTable(string $path, string $user, string $password, string $tablename): void
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

    foreach ($results as $row) {

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
// Update using Transactions and Exceptions
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

    // Input Validation
    if ($nodeID <= 0) {
        throw new Exception("Invalid Node ID.");
    }

    if ($newCurrentFloor < 1 || $newCurrentFloor > 3) {
        throw new Exception("Current floor must be between 1 and 3.");
    }

    if ($newRequestedFloor < 1 || $newRequestedFloor > 3) {
        throw new Exception("Requested floor must be between 1 and 3.");
    }

    $db = connect($path, $user, $password);
    try {
        // Start Transaction
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

        // Verify a record was actually updated
        if ($statement->rowCount() === 0) {
            throw new Exception(
                "Update failed. Node ID {$nodeID} was not found."
            );
        }

        // Commit Transaction
        $db->commit();

        echo "Update successful.";

    } catch (Exception $e) {

        // Rollback on Error
        $db->rollBack();

        throw new Exception(
            "Transaction Failed: " . $e->getMessage()
        );
    }
}

// Delete
function delete(
    string $path,
    string $user,
    string $password,
    int $nodeID
): void {

    $db = connect($path, $user, $password);

    $query = "DELETE FROM CANLogs WHERE nodeID = :nodeID";

    $statement = $db->prepare($query);

    $statement->execute([
        'nodeID' => $nodeID
    ]);
}

function get_currentFloor(string $path, string $user, string $password): int
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

function getMaintenanceStatus(int $recordCount): array
{
    $status = [
        'inspection' => false,
        'warning' => false,
        'maintenance' => false,
        'message' => 'Normal Operation'
    ];

    if ($recordCount >= 30000) {

        $status['maintenance'] = true;
        $status['message'] =
            "Maintenance Mode Activated Automatically";

    } elseif ($recordCount >= 20000) {

        $status['warning'] = true;
        $status['message'] =
            "WARNING: Elevator approaching maintenance interval";

    } elseif ($recordCount >= 10000) {

        $status['inspection'] = true;
        $status['message'] =
            "Inspection Required";
    }

    return $status;
}

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
        ORDER BY nodeID DESC
        LIMIT 1
    ";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'mode' => $mode
    ]);

}