<?php

// Start the session.
session_start();

// Include the Autoloader class.
require_once __DIR__ . "/../app/core/Autoloader.php";

// Register the autoloader.
App\Core\Autoloader::register();

// Load environment variables.
App\Core\EnvLoader::load();

// Register the exception handler.
App\Core\ExceptionHandler::register();

// Initialize the Logger and log application start.
$logger = App\Core\Logger::getInstance();
$logger->info("Application started.");

// Initialize and run the application.
$app = new App\Core\Application();

// Register middleware.
$app->addMiddleware(new \App\Middlewares\UidCookieMiddleware());

// Run the application.
$app->run();

// Log application end.
$logger->info("Application ended.");
