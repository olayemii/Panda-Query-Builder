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

    private $_dbh;

    public function testSelectFetchesRealRecords(){
        $this->_dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($this->_dbh->query("SELECT `email` FROM `users`")->fetchAll(), QB::table("users")->select("email")->get(), "Strings are not the same");
    }

    public function testSelectFetchesRealRecordsNoArgs(){
        $this->_dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($this->_dbh->query("SELECT * FROM `users`")->fetchAll(), QB::table("users")->select("*")->get(), "Strings are not the same");
    }

    public function testSelectFetchesRealRecordsWithWhere(){
        $this->_dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($this->_dbh->query("SELECT * FROM `users` WHERE `id` > 4")->fetchAll(), QB::table("users")->select("*")->where(["id",">", "4"])->get(), "Strings are not the same");
    }

    public function testInnerJoin(){
        $this->_dbh = \App\Classes\Registry::run("pdo");
        $this->assertSame($this->_dbh->query("SELECT * FROM `users` JOIN `countries` ON `countries`.`user_id` = `users`.`id`")->fetchAll(), QB::table("users")->select("*")->join("countries", "countries.user_id", "=", "users.id")->get(), "Strings are not the same");
    }
}
