<?php

require __DIR__."/src/Core/bootstrap.php";

use App\Classes\Database;
use App\Classes\QueryBuilder;

$userTable = new QueryBuilder("users");

echo $userTable->where([
    ["id", "20"]])->update(["name" => "OLayemii"]);