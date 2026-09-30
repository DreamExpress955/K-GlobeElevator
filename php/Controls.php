<?php

/**
 * @file
 * @brief Provides the troubleshooting and manual elevator control page.
 *
 * This page provides controls for manually requesting elevator floors
 * from both the floor controller and car controller.
 *
 * The page communicates with the Elevator MySQL database to read the
 * elevator's current floor and update floor requests. The current floor
 * is displayed using red and green floor indicators.
 *
 * The page also contains a temporary queue section for future elevator
 * request queue functionality.
 */

/**
 * @brief Updates the current floor for an elevator network node.
 *
 * Connects to the Elevator database and updates the currentFloor field
 * in the elevatorNetwork table for the specified node.
 *
 * A prepared statement is used to safely pass the floor number and
 * node ID to the database query.
 *
 * @param int $node_ID ID of the elevator network node to update.
 * @param int $new_floor New floor value to store. Defaults to floor 1.
 *
 * @return int The floor number that was stored in the database.
 */
function update_elevatorNetwork(int $node_ID, int $new_floor = 1): int
{
    $db = get_database();

    $query = '
        UPDATE elevatorNetwork
        SET currentFloor = :floor
        WHERE nodeID = :id
    ';

    $statement = $db->prepare($query);

    $statement->bindValue(
        ':floor',
        $new_floor,
        PDO::PARAM_INT
    );

    $statement->bindValue(
        ':id',
        $node_ID,
        PDO::PARAM_INT
    );

    $statement->execute();

    return $new_floor;
}

/**
 * @brief Gets the current elevator floor from the database.
 *
 * Connects to the Elevator MySQL database and retrieves the currentFloor
 * value from the elevatorNetwork table.
 *
 * @return int The current floor stored in the elevatorNetwork table.
 */
function get_currentFloor(): int
{
    try
    {
        $db = new PDO(
            'mysql:host=127.0.0.1;dbname=Elevator',
            'myphpadmin',
            'YOUR_DATABASE_PASSWORD'
        );
    }
    catch (PDOException $e)
    {
        echo $e->getMessage();
    }

    // Query the database to retrieve the current elevator floor.
    $rows = $db->query(
        'SELECT currentFloor FROM elevatorNetwork'
    );

    foreach ($rows as $row)
    {
        $current_floor = $row[0];
    }

    return $current_floor;
}

/**
 * @brief Creates a connection to the Elevator MySQL database.
 *
 * Creates and returns a PDO connection configured to throw exceptions
 * when database errors occur and to return query results as associative
 * arrays.
 *
 * @return PDO Connection to the Elevator database.
 */
function get_database(): PDO
{
    return new PDO(
        'mysql:host=127.0.0.1;dbname=Elevator;charset=utf8mb4',
        'myphpadmin',
        'YOUR_DATABASE_PASSWORD',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

// Store any error message generated while processing a request.
$errorMessage = '';

// Process elevator floor requests submitted by the page.
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    try
    {
        // Determine which controller submitted the request.
        $requestType = $_POST['request_type'] ?? '';

        // Validate the requested floor.
        // Only floors 1 through 3 are accepted.
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

        // Reject an invalid floor request.
        if (
            $requestedFloor === false ||
            $requestedFloor === null
        )
        {
            throw new InvalidArgumentException(
                'The selected floor is invalid.'
            );
        }

        // Determine which elevator network node should be updated.
        if ($requestType === 'floor_controller')
        {
            /*
             * Floor-controller requests currently update node 4.
             */
            $nodeID = 4;
        }
        elseif ($requestType === 'car_controller')
        {
            /*
             * All three car buttons currently update
             * the car-controller node.
             */
            $nodeID = 4;
        }
        else
        {
            throw new InvalidArgumentException(
                'The request type is invalid.'
            );
        }

        // Update the elevator network with the requested floor.
        update_elevatorNetwork(
            $nodeID,
            $requestedFloor
        );

        // Prevent duplicate form submission when the page is refreshed.
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    catch (Throwable $error)
    {
        $errorMessage = $error->getMessage();
    }
}

// Get the current elevator floor for the floor indicators.
try
{
    $curFlr = get_currentFloor();
}
catch (Throwable $error)
{
    $curFlr = 1;
    $errorMessage = $error->getMessage();
}

?>

<html>
    <head>
        <title>Trouble Shooting</title>

        <meta http-equiv="author" content="Owen Kipp" />

        <!-- Want browser to reload this page every time. -->
        <meta http-equiv="pragma" content="no-cache" />

        ../css/request.css
    </head>

    <h1>K-Globe</h1>

    <fieldset>
        <legend>Elevator Control</legend>

        <?php

        /*
         * Display the current floor using red and green indicators.
         *
         * The current floor is shown using the green indicator.
         * All other floors are shown using red indicators.
         */

        $curFlr = get_currentFloor();

        if ($curFlr == 1)
        {
            echo '<div class ="img-grid">
                ../Pics/GREEN.png
                ../Pics/RED.png
                ../Pics/RED.png
                </div>';
        }
        elseif ($curFlr == 2)
        {
            echo '<div class ="img-grid">
                ../Pics/RED.png
                ../Pics/GREEN.png
                ../Pics/RED.png
                </div>';
        }
        elseif ($curFlr == 3)
        {
            echo '<div class ="img-grid">
                ../Pics/RED.png
                ../Pics/RED.png
                ../Pics/GREEN.png
                </div>';
        }
        else
        {
            echo '<div class ="img-grid">
                ../Pics/RED.png
                ../Pics/RED.png
                ../Pics/RED.png
                </div>';
        }

        ?>

        <h2>

            <div display ="flex">

                <fieldset>
                    <legend>Request a floor</legend>

                    

                        <input
                            type="hidden"
                            name="request_type"
                            value="floor_controller"
                        >

                        <button
                            type="submit"
                            name="floor"
                            value="1"
                        >
                            Floor 1
                        </button>

                        <button
                            type="submit"
                            name="floor"
                            value="2"
                        >
                            Floor 2
                        </button>

                        <button
                            type="submit"
                            name="floor"
                            value="3"
                        >
                            Floor 3
                        </button>

                    </form>

                </fieldset>

            </div>

            <fieldset>
                <legend>Car Controller</legend>

                

                    <input
                        type="hidden"
                        name="request_type"
                        value="car_controller"
                    >

                    <button
                        type="submit"
                        name="floor"
                        value="1"
                    >
                        Floor 1
                    </button>

                    <button
                        type="submit"
                        name="floor"
                        value="2"
                    >
                        Floor 2
                    </button>

                    <button
                        type="submit"
                        name="floor"
                        value="3"
                    >
                        Floor 3
                    </button>

                </form>

            </fieldset>

            <fieldset>
                <legend>Queue</legend>

                <ol>
                    <li>TEMP</li>
                </ol>

            </fieldset>

        </h2>

    </fieldset>

</html>