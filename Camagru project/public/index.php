<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/router.php';
require_once __DIR__ . '/../app/core/controller.php';

$router = new Router();
$router->dispatch();
