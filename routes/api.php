<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataJson\JsonController;

$routes = glob(__DIR__ . "/api/*.php");
foreach ($routes as $route) require($route);

