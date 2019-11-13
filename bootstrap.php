<?php
const ROOT = __DIR__;
$loader = require_once ROOT . '/vendor/autoload.php';

use App\System\Database;
use Dotenv\Dotenv;

$dotenv = Dotenv::create(ROOT);
$dotenv->load();

$db = new Database();