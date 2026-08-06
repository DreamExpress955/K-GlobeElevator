<?php

trait LoggerTrait
{
    public function logMessage($message)
    {
        echo "[LOG] " . $message . "<br>";
    }
}

?>
