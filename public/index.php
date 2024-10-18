<?php

// Include the Autoloader class.
require_once __DIR__ . "/../app/core/Autoloader.php";

// Register the autoloader.
Autoloader::register();

// Load environment variables.
App\Core\EnvLoader::load();

// Register the exception handler.
App\Core\ExceptionHandler::register();

// Initialize and run the application.
$app = new App\Core\Application();
$app->run();
