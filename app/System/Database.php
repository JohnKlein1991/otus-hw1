<?php
/**
 * Подключение к БД
 *
 */
namespace App\System;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public function __construct()
    {
        $host = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $host,
            'database'  => $dbName,
            'username'  => $user,
            'password'  => $password,
            'charset'   => 'utf8',
//            'collation' => 'utf8_unicode_ci',
//            'prefix'    => '',
        ]);

        $capsule->bootEloquent();
    }
}