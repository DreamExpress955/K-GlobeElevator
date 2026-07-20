<?php 
// CRUD (Create, Read, Update, Delete) functions
function connect(string $path, string $user, string $password) {
    $db = new PDO($path,$user, $password);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db; 
}
// Create
function insert($path, $user, $password, $nodeID, $current_date, $current_time, $status, $currentFloor, $requestedFloor, $otherInfo) {
    $db = connect($path, $user, $password);
$query = 'INSERT INTO elevatorNetwork(
    nodeID,
    date,
    time,
    status,
    currentFloor,
    requestedFloor,
    otherInfo
) VALUES (
    :nodeID,
    :date,
    :time,
    :status,
    :currentFloor,
    :requestedFloor,
    :otherInfo)';
    $params = [
    'nodeID' => $nodeID,
    'date' => $current_date,
    'time' => $current_time,
    'status' => $status,
    'currentFloor' => $currentFloor,
    'requestedFloor' => $requestedFloor,
    'otherInfo' => $otherInfo
];
    $statement = $db->prepare($query);
    $result = $statement->execute($params); 
}
// Read
function showtable(string $path, string $user, string $password, $tablename) {
    echo "<h3>Content of ElevatorNetwork table</h3>";
    $db = connect($path, $user, $password); 
    $query = "SELECT * FROM $tablename ORDER BY logID DESC";  // Note: Risk of SQL 
    $rows = $db->query($query); 
    echo "DATE|TIME|NODEID|STATUS|CURRENTFLOOR|REQUESTED FLOOR|OTHERINFO <br>";
    foreach ($rows as $row) {
        echo $row['Date'] . " |  " . $row['Time'] . " | " . $row['nodeID'] . " | " . $row['Status'] . " | " 
             . $row['CurrentFloor'] . " | " . $row['RequestedFloor'] . " | " . $row['OtherInfo'] . "<br>";
    }
}
// Update
function update(string $path, string $user, string $password, string $tablename, int $node_ID, int $new_status, int $new_currentFloor, 
                int $new_requestedFloor, string $new_otherInfo) : void {
    $db = connect($path, $user, $password);
    $query = 'UPDATE ' . $tablename . ' SET status = :stat, currentFloor = :curFloor, requestedFloor = :rqFloor, otherInfo = :oInfo
             WHERE nodeID = :id' ;    // Note: Risks of SQL injection
    $statement = $db->prepare($query); 
    $statement->bindValue('stat', $new_status); 
    $statement->bindValue('curFloor', $new_currentFloor);
    $statement->bindValue('rqFloor', $new_requestedFloor);
    $statement->bindValue('oInfo', $new_otherInfo);
    $statement->bindValue('id', $node_ID); 
    $statement->execute();                      // Execute prepared statement
}
// Delete
function delete(string $path, string $user, string $password, string $tablename, int $node_ID) : void {
    $db = connect($path, $user, $password);
    $query = 'DELETE FROM ' . $tablename . ' WHERE nodeID = :id' ;    // Note: Risks of SQL injection
    $statement = $db->prepare($query); 
    $statement->bindValue('id', $node_ID); 
    $statement->execute();                      // Execute prepared statement
}

?>