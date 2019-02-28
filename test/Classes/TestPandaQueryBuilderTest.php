<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/25/2019
 * Time: 11:46 PM
 */
use App\Classes\QueryBuilder as QB;

class TestPandaQueryBuilderTest extends \PHPUnit\Framework\TestCase
{


    public function testSelectFetchesRealRecords(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT `email` FROM `users`")->fetchAll(), QB::table("users")->select("email")->get(), "Strings are not the same");
    }

    public function testSelectFetchesRealRecordsNoArgs(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM `users`")->fetchAll(), QB::table("users")->select("*")->get(), "Strings are not the same");
    }

    public function testSelectFetchesRealRecordsWithWhere(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM `users` WHERE `id` > 4")->fetchAll(), QB::table("users")->select("*")->where(["id",">", "4"])->get(), "Strings are not the same");
    }

    public function testInnerJoin(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM users JOIN countries ON countries.user_id = users.id JOIN migrations ON migrations.id = users.id
")->fetchAll(), QB::table("users")->select("*")->join("countries", "countries.user_id", "=", "users.id")->join("migrations", "migrations.id", "=", "users.id")->get(), "Strings are not the same");
    }

    public function testMixedJoin(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM users JOIN countries ON countries.user_id = users.id RIGHT JOIN migrations ON migrations.id = users.id
")->fetchAll(), QB::table("users")->select("*")->join("countries", "countries.user_id", "=", "users.id")->rightJoin("migrations", "migrations.id", "=", "users.id")->get(), "Strings are not the same");
    }
    
    public function testFirstReturnedRow(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM `users` LIMIT 1")->fetchAll(), QB::table("users")->first());
    }

    public function testGroupBy(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM `countries` GROUP BY name")->fetchAll(), QB::table("countries")->groupBy("name")->get());
    }

    public function testOrderBy(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT * FROM `countries` ORDER BY id DESC")->fetchAll(), QB::table("countries")->orderBy("id", "desc")->get());
    }

    public function testCountReturn(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT COUNT(*) FROM `countries`")->fetchColumn(), QB::table("countries")->count());
    }

    public function testDistinctResult(){
        $dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($dbh->query("SELECT DISTINCT * FROM `countries`")->fetchAll(), QB::table("countries")->distinct()->get());
    }
}
