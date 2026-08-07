<?php

require_once("ElevatorController.php");

$controller = new ElevatorController();

try
{
    $controller->validateNode(1);

    $controller->checkConnection(true);

    $controller->readSensor(25);

    $controller->requestFloor(4);
}
catch (InvalidFloorException $e)
{
    echo "<b>INVALID FLOOR ERROR:</b> "
         . $e->getMessage();
}
catch (CommunicationException $e)
{
    echo "<b>COMMUNICATION ERROR:</b> "
         . $e->getMessage();
}
catch (SensorException $e)
{
    echo "<b>SENSOR ERROR:</b> "
         . $e->getMessage();
}
catch (NodeException $e)
{
    echo "<b>NODE ERROR:</b> "
         . $e->getMessage();
}
catch (Exception $e)
{
    echo "<b>GENERAL ERROR:</b> "
         . $e->getMessage();
}

?>