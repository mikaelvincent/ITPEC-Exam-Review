<?php

// Include the Autoloader class.
require_once __DIR__ . "/../app/core/Autoloader.php";

// Register the autoloader.
Autoloader::register();

// Initialize and run the application.
$app = new App\Core\Application();
$app->run();
