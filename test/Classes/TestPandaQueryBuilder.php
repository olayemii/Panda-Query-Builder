<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/25/2019
 * Time: 11:46 PM
 */
use App\Factories\QB;

class TestPandaQueryBuilder extends \PHPUnit\Framework\TestCase
{
    public function testSelectStatementReturnsExpectedQuery(){
        $this->assertSame("SELECT `name`, `age` FROM `users`", QB::table("users")->select("name", "age")->getSql(), "Strings are not the same");
    }

    public function testInsertStatementReturnsExpectedQuery(){
        $this->assertSame("INSERT INTO `users`(name, age) VALUES('OLayemii', 21)", QB::table("users")->insert(["name" => "OLayemii", "age" => 21]));
    }
}
