<?php
	function update_elevatorNetwork(int $node_ID, int $new_floor =1): int {
		$db = get_database();

    $query = '
        UPDATE elevatorNetwork
        SET currentFloor = :floor
        WHERE nodeID = :id
    ';

    $statement = $db->prepare($query);
    $statement->bindValue(':floor', $new_floor, PDO::PARAM_INT);
    $statement->bindValue(':id', $node_ID, PDO::PARAM_INT);
    $statement->execute();

    return $new_floor;
		
	}
?>
<?php 
	function get_currentFloor(): int {
		try { $db = new PDO('mysql:host=127.0.0.1;dbname=elevator','myphpadmin','ese1');}
		catch (PDOException $e){echo $e->getMessage();}           
			// Query the database to display current floor
			$rows = $db->query('SELECT currentFloor FROM elevatorNetwork');
			foreach ($rows as $row) {
				$current_floor = $row[0];
			}
			return $current_floor;
	}
?>
<?php
function get_database(): PDO
{
    return new PDO(
        'mysql:host=127.0.0.1;dbname=elevator;charset=utf8mb4',
        'myphpadmin',
        'ese1',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $requestType = $_POST['request_type'] ?? '';
        $requestedFloor = filter_input(
            INPUT_POST,
            'floor',
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 3
                ]
            ]
        );

        if ($requestedFloor === false || $requestedFloor === null) {
            throw new InvalidArgumentException('The selected floor is invalid.');
        }

        if ($requestType === 'floor_controller') {
            /*
             * Floor-controller requests:
             */
            $nodeID = $requestedFloor;
        } elseif ($requestType === 'car_controller') {
            /*
             * All three car buttons update the car-controller node.
             */
            $nodeID = 4;
        } else {
            throw new InvalidArgumentException('The request type is invalid.');
        }

        update_elevatorNetwork($nodeID, $requestedFloor);

        // Prevent duplicate form submission when the page is refreshed.
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch (Throwable $error) {
        $errorMessage = $error->getMessage();
    }
}

try {
    $curFlr = get_currentFloor();
} catch (Throwable $error) {
    $curFlr = 1;
    $errorMessage = $error->getMessage();
}
?>


<html>
	<head><title>Trouble Shooting</title>
    <meta name="description" content="This is the Request acess page to allow the creation of a login" />
    <meta name="robots" content="noindex nofollow" />  <!-- do not want page or any of its links to be indexed -->
    <meta http-equiv="author" content="Blake Gergely" />
    <meta http-equiv="pragma" content="no-cache" /> <!-- want browser to reload this page every time -->
    <link rel="stylesheet" href="../css/request.css">
	</head>
	<h1>K-Globe</h1> 
	
		<?php 
			if(isset($_POST['newfloor'])) {
				$curFlr = update_elevatorNetwork(1, $_POST['newfloor']); 
				header('Refresh:0; url=index.php');	
			} 
			$curFlr = get_currentFloor();
            
			echo "<h2>Current floor # $curFlr </h2>";			
		?>		
		
		<h2> 
			<feildset>
				<legend>Remote Control</legend>
				<div>
			<feildset>
				<legend>Request a floor</legend>
			<form action="" method="POST" class="button-grid">
                <input type="hidden" name="request_type" value="floor_controller">

				<button type="submit" name="floor" value="1">Floor 1</button>
				<button type="submit" name="floor" value="2">Floor 2</button>
				<button type="submit" name="floor" value="3">Floor 3</button>

			</form>
				
			</feildset>
                </div>
                <div>
			<feildset>
				<legend>Car Controller</legend>
			<form action="" method="POST" class="button-grid">
				<input type="hidden" name="request_type" value="car_controller">

				<button type="submit" name="floor" value="1">Floor 1</button>
				<button type="submit" name="floor" value="2">Floor 2</button>
				<button type="submit" name="floor" value="3">Floor 3</button>
			</form>
			</feildset>
            </div>
			</feildset>

		</h2>
		  
		
</html>
 
 
