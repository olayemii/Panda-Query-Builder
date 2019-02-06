<?php

    require 'vendor/autoload.php';

    use App\Classes\Database;
    use App\Classes\QueryBuilder;

    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

   
    // var_dump(Database::getInstance());

    $qb = new QueryBuilder();
    $d = $qb->table("users")->where(null, [
        ["name", "=", "OLa"],
        ["age", "=", "22"],
    ])->orWhere("name", "Temitope")->orWhere("beards", '=', "None");


    var_dump($d->get());