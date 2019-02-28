<?php

require __DIR__."/src/Core/bootstrap.php";
use App\Classes\QueryBuilder as QB;
use App\Classes\Registry;
//$userTable = new PandaQB("users");
//QB::registerEvent("before-insert", "users", function(){
//    echo "Hello Insert";
//});

//QB::registerEvent("after-insert", "users", function($index){
//   QB::table("countries")->insert(["name" => "United Kingdom", "shortcode" => "LDN", "user_id" => $index]);
//});
//
//QB::registerEvent("before-insert", "users", function(){
//    echo "Inserting a new user with a country";
//});

//echo json_encode(QB::table("users")->insert(["name" => "Ella Egwuatu", "email" => "ellamillion85@gmail.com", "password" => "dazzlingellla"]));
//
//echo QB::table("users")->select("name")->distinct()->getSql();
//
//echo QB::table("users")->select("users.name", "countries.name", "user.age")->leftJoin("countries", "users.id", "=", "countries.id")->getSql();
//

//Registry::register("pdo", function (){
//    echo "Hello World!!";
//});
//
//Registry::run("pdo");

var_dump(QB::table("countries")->select("name")->distinct()->getSql());




//QB::table("users")->select("*")->join("countries", "countries.user_id", "=", "users.id")->join("migrations", "migraes.id", "=", "migr.uid")->getSql();