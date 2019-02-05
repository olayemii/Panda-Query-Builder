<?php

    require 'vendor/autoload.php';

    use App\Classes\Database;
    use App\Classes\QueryBuilder;

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

   
    // var_dump(Database::getInstance());

    $qb = new QueryBuilder();
    $d = $qb->table("users")->where("name", "=", "OLayemii");


    var_dump($d->get());