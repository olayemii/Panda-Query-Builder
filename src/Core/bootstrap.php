<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/7/2019
 * Time: 2:13 AM
 */

//Bootstrap the autoloader
require __DIR__.'/../../vendor/autoload.php';

use App\Classes\Database;
//Load environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__."/../../");
$dotenv->load();

//Load
Database::registerDatabase();
