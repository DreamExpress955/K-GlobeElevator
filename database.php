<?php
// Query a database and print results slide 2
$db = new pdo(
    'mysql:host=127.0.0.1;dbname=elevator',
    'root',
    'ese'
);
    // return arrays with keys that are the name of the fields
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $rows = $db->query('SELECT * FROM elevatorNetwork ORDER BY nodeIT');
    foreach($rows as $row)
        {
        var_dump($row);
        echo "<br><br>";
    }
?>

<?php
// Formatted Query slide 2
$db = new pdo(
    'mysql:host=127.0.0.1;dbname=elevator',
    'root',
    'ese'
);
    // return arrays with keys that are the name of the fields
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $query = 'SELECT * FROM elevatorNetwork WHERE nodeID = :nodeID';
    $statement = $db->prepare($query);
    $statement->bindValue('nodeID', 1);
    $result = $statement->execute();
    $rows = $statement->fetchAll();

    $rows = $db->query('SELECT * FROM elevatorNetwork ORDER BY nodeIT');
    foreach($rows as $row)
        {
        var_dump($row);
        echo "<br><br>";
    }

?>

<?php
// Insert static data slide 2
$db = new pdo(
    'mysql:host=127.0.0.1;dbname=elevator',
    'root',
    'ese'
);
    // return arrays with keys that are the name of the fields
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $query = 'INSERT INTO elevatorNetwork
        (date, time, nodeID, status, currentFloor, requestedFloor, OtherInfo)
        VALUES("2026/07/13", "6:47:00", 5, 1, 1, 1, "Test Insert")';

// error checking remeber exec only returns the true or false
    $result = $db->exec($query);

    if($result === false)
        {
        $errorInfo = $db->errorInfo();
        echo "Error inserting data: " . $errorInfo[2];
        }
    else
        {
        echo "Inserted $result rows";
        var_dump($result);
        }

    $rows = $db->query('SELECT * FROM elevatorNetwork ORDER BY nodeIT');
    foreach($rows as $row)
        {
        var_dump($row);
        echo "<br><br>";
    }

?>

<?php
// Insert dynamic with live dates & time data slide 2
$db = new pdo(
    'mysql:host=127.0.0.1;dbname=elevator',
    'root',
    'ese'
);
    // return arrays with keys that are the name of the fields
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // static queries for data and time
    $querytime = "SELECT CURRENT_TIME()";
    $timeResult = $db->query($querytime);
    $currentTime = $timeResult->fetch()['CURRENT_TIME()'];
    var_dump($currentTime);
    $querydate = "SELECT CURRENT_DATE()";
    $dateResult = $db->query($querydate);
    $currentDate = $dateResult->fetch()['CURRENT_DATE()'];
    var_dump($currentDate);


    $query = 'INSERT INTO elevatorNetwork
        (date, time, nodeID, status, currentFloor, requestedFloor, OtherInfo)
        VALUES(:date, :time, :nodeID, :status, :currentFloor, :requestedFloor, :OtherInfo)';

    $parameters = [
        'date' => $currentDate,
        'time' => $currentTime,
        'nodeID' => 5,
        'status' => 1,
        'currentFloor' => 1,
        'requestedFloor' => 1,
        'OtherInfo' => 'Test Insert'
    ];

    $result = $statement->execute($parameters);

    $rows = $db->query('SELECT * FROM elevatorNetwork ORDER BY nodeIT');
    foreach($rows as $row)
        {
        var_dump($row);
        echo "<br><br>";
    }
// end of slides 2
?>

<?php
// Joining tables slide 3
$db = new pdo(
    'mysql:host=127.0.0.1;dbname=elevator',
    'root',
    'ese'
);
    // return arrays with keys that are the name of the fields
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


    // exaple ordering output by nodeID
    $query = 'SELECT * FROM elevatorNetwork ORDER BY nodeID';
    $rows = $db->query($query);
    echo "| nodeID | date | time | status | currentFloor | requestedFloor | OtherInfo |<br>";
    foreach($rows as $row)
        {
        echo str_repeat('&nbsp;', 6 . $row["nodeID"] . str_repeat('&nbsp;', 17) . $row["date"] . str_repeat('&nbsp;', 17) . $row["time"] . str_repeat('&nbsp;', 17) . $row["status"] . str_repeat('&nbsp;', 17) . $row["currentFloor"] . str_repeat('&nbsp;', 17) . $row["requestedFloor"] . str_repeat('&nbsp;', 17) . $row["OtherInfo"] . "<br>");
        }
    echo "<br><br>";

    // example grouping output
    $query = 'SELECT nodeID, COUNT(*) AS hits FROM elevatorNetwork GROUP BY nodeID';
    $rows = $db->query($query);
    echo "| nodeID | hits |<br>";
    foreach($rows as $row)
        {
        echo "| " . $row['nodeID'] . " | " . $row['hits'] . " |<br>";
        }
    echo "<br><br>";
?>

