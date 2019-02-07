<?php

    require __DIR__."/src/Core/bootstrap.php";

    use App\Classes\Database;
    use App\Classes\QueryBuilder;

    $userTable = new QueryBuilder("users");


    echo json_encode($userTable->where([
        ["name", "=", "OLayemii"],
        ["age",  ">", "21"],
    ])->orWhere("name", "Teegar")->pluck('id, age, gender')->orderBy("id")->first()->get());