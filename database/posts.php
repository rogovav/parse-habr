<?php

require "bootstrap.php";

use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('posts', function ($table) {
    $table->increments('id');
    $table->string('title');
    $table->string('short');
    $table->longText('body');
    $table->string('link')->unique();
    $table->timestamps();
});