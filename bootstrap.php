<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

define("ROOT_DIR", dirname(__FILE__));
define("UPLOAD_DIR", ROOT_DIR . "/uploads/");
