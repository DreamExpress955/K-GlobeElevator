<?php
// CRUD functions for database operations

function connect(string $path, string $user, string $password)
{
    $db = new pdo($path, $user, $password);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
}
// Create
function insertData($path, $user, $password, $current_date, $current_time, $nodeID, $status, $currentFloor, $requestedFloor, $OtherInfo)
{
    $db = connect($path, $user, $password);
    $query = 'INSERT INTO elevatorNetwork
        (date, time, nodeID, status, currentFloor, requestedFloor, OtherInfo)
        VALUES(:date, :time, :nodeID, :status, :currentFloor, :requestedFloor, :OtherInfo)'; // at risk o f SQL injection if not using prepared statements
    $params = [
        ':date' => $current_date['CURRENT_DATE()'],
        ':time' => $current_time['CURRENT_TIME()'],
        ':nodeID' => $nodeID,
        ':status' => $status,
        ':currentFloor' => $currentFloor,
        ':requestedFloor' => $requestedFloor,
        ':OtherInfo' => $OtherInfo
    ];
    $statement = $db->prepare($query);
    $result = $statement->execute($params);
    return $result;
}
// Read
function readData(string $path, string $user, string $password, $tablename)
{
    echo "<h3>Read Data</h3>";
    $db = connect($path, $user, $password);
    $query = "SELECT * FROM $tablename GROUP BY nodeID ORDER BY nodeID";
    $rows = $db->prepare($query);
    $rows->execute();
    echo "DATE|TIME|NODEID|STATUS|CURRENTFLOOR|REQUESTEDFLOOR|OTHERINFO<br>";
    foreach($rows as $row)
        {
        echo $row['date'] . "|" . $row['time'] . "|" . $row['nodeID'] . "|" . $row['status'] . "|" . $row['currentFloor'] . "|" . $row['requestedFloor'] . "|" . $row['OtherInfo'] . "<br>";
        }
}
// Update
function updateData(
    string $path,
    string $user,
    string $password,
    string $tablename,
    int $node_ID,
    int $new_status,
    int $new_currentFloor,
    int $new_requestedFloor,
    string $new_OtherInfo
): void {
    $db = connect($path, $user, $password);

    $query = "UPDATE $tablename
              SET status = :new_status,
                  currentFloor = :new_currentFloor,
                  requestedFloor = :new_requestedFloor,
                  OtherInfo = :new_OtherInfo
              WHERE nodeID = :node_ID";

    $statement = $db->prepare($query);

    $statement->bindValue(':new_status', $new_status, PDO::PARAM_INT);
    $statement->bindValue(':new_currentFloor', $new_currentFloor, PDO::PARAM_INT);
    $statement->bindValue(':new_requestedFloor', $new_requestedFloor, PDO::PARAM_INT);
    $statement->bindValue(':new_OtherInfo', $new_OtherInfo, PDO::PARAM_STR);
    $statement->bindValue(':node_ID', $node_ID, PDO::PARAM_INT);

    $statement->execute();
}
// Delete
function deleteData(string $path, string $user, string $password, string $tablename, int $node_ID):void
{
    $db = connect($path, $user, $password);
    $query = "DELETE FROM $tablename WHERE nodeID = :node_ID";
    $statement = $db->prepare($query);
    $statement->bindValue(':node_ID', $node_ID);
    $statement->execute();
}




?>