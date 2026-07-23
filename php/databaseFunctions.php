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

    $db = connect($path, $user, $password);

    $query = "SELECT * FROM $tablename";
    $statement = $db->query($query);

    $results = $statement->fetchAll();

    echo "<h4 class='mb-3'>Content of ElevatorNetwork Table</h4>";

    echo "<table class='table table-striped table-hover table-bordered'>";

    echo "<thead class='table-dark'>";
    echo "<tr>";
    echo "<th>Date</th>";
    echo "<th>Time</th>";
    echo "<th>Node ID</th>";
    echo "<th>Status</th>";
    echo "<th>Current Floor</th>";
    echo "<th>Requested Floor</th>";
    echo "<th>Other Info</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";

foreach ($results as $row) {

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Time']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nodeID']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
    echo "<td>" . htmlspecialchars($row['CurrentFloor']) . "</td>";
    echo "<td>" . htmlspecialchars($row['RequestedFloor']) . "</td>";
    echo "<td>" . htmlspecialchars($row['OtherInfo']) . "</td>";
    echo "</tr>";

}

    echo "</tbody>";
    echo "</table>";
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

function get_currentFloor($path, $user, $password)
{
    $db = connect($path, $user, $password);

    $query = "SELECT CurrentFloor
              FROM elevatorNetwork
              ORDER BY logID DESC
              LIMIT 1";

    $statement = $db->query($query);

    $result = $statement->fetch();

    if ($result) {
        return $result['CurrentFloor'];
    }

    return 0;
}

?>