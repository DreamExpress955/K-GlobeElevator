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
        INSERT INTO elevatorNetwork (
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
FROM elevatorNetwork e
LEFT JOIN canNetwork c
    ON e.nodeID = c.nodeID
";
    $statement = $db->query($query);

    $results = $statement->fetchAll();

    echo "<h4 class='mb-3'>Content of ElevatorNetwork Table</h4>";

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
        UPDATE elevatorNetwork
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
            UPDATE elevatorNetwork
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

    $query = "DELETE FROM elevatorNetwork WHERE nodeID = :nodeID";

    $statement = $db->prepare($query);

    $statement->execute([
        'nodeID' => $nodeID
    ]);
}

function get_currentFloor(string $path, string $user, string $password): int
{
    $db = connect($path, $user, $password);

    $query = "
        SELECT CurrentFloor
        FROM elevatorNetwork
        ORDER BY nodeID DESC
        LIMIT 1";

    $statement = $db->query($query);

    $result = $statement->fetch();

    return $result ? (int)$result['CurrentFloor'] : 0;
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
    INSERT INTO canNetwork (
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
    UPDATE canNetwork
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
    DELETE FROM canNetwork
    WHERE canID = :canID";

    $stmt = $db->prepare($query);

    $stmt->execute([
        'canID' => $canID
    ]);
}

function showCANTable(
    string $path,
    string $user,
    string $password
): void {

    $db = connect($path, $user, $password);

    $query = "SELECT * FROM can";

    $statement = $db->query($query);

    $results = $statement->fetchAll();

    echo "<h4 class='mb-3'>CAN Network Table</h4>";

    echo "<table class='table table-striped table-hover table-bordered'>";

    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th>CAN ID</th>";
    echo "<th>Node ID</th>";
    echo "<th>Message ID</th>";
    echo "<th>Baud Rate</th>";
    echo "<th>Last Message</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";

    foreach ($results as $row) {

        echo "<tr>";

        echo "<td>" . htmlspecialchars($row['canID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nodeID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['messageID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['baudRate']) . "</td>";
        echo "<td>" . htmlspecialchars($row['lastMessage']) . "</td>";

        echo "</tr>";
    }

    echo "</tbody>";

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
            e.CurrentFloor,
            e.RequestedFloor,
            e.OtherInfo,
            c.canID,
            c.messageID,
            c.baudRate,
            c.lastMessage
        FROM elevatorNetwork e
        LEFT JOIN can c ON e.nodeID = c.nodeID
    ";
}


?>