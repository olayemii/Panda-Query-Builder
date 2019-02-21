<?php

require __DIR__."/src/Core/bootstrap.php";

use App\Classes\QueryBuilder as PandaQB;

$userTable = new PandaQB("users");

var_dump($userTable->select()->count());