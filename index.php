<?php

    require __DIR__."/src/Core/bootstrap.php";

    use App\Classes\Database;
    use App\Classes\QueryBuilder;

    $userTable = new QueryBuilder("users");


//    echo json_encode($userTable->where([
//        ["name", "=", "OLayemii"],
//        ["age",  ">", "21"],
//    ])->orWhere("name", "Teegar")->pluck('id, age, gender')->orderBy("id")->first()->get());
//    echo($userTable->select("name", "age")->buildQuery());
//if ($userTable->insert(["name" => "Teegar", "age" => "19", "eye_color" => "black", "height" => "1.5"])){
//    echo "Record successfully inserted";
//}else{
//    echo "Noooo!!".$userTable->getError();
//}
//$res = $userTable->delete();
//
//var_dump($res);

$userTable->select()->where([
    ["name", "OLayemii"],
    ["age", "21"],
])->orWhere([
    ["name", "OLayemii"],
    ["age", "21"],
])->getConditionals();