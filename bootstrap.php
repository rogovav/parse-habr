<?php

require "simple_html_dom.php";
require "vendor/autoload.php";

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'database'  => 'habr',
    'username'  => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'port' => '8889'
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

foreach (glob("models/*.php") as $filename)
{
    include $filename;
}
