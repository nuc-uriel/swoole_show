<?php

use App\Services\HttpService;

require_once __DIR__ . "/app/Services/HttpService.php";
$httpServer = new HttpService();
$httpServer->init()->start();
