<?php

    require 'vendor/autoload.php';

    use App\Classes\Database;

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

   
    var_dump(Database::getInstance());