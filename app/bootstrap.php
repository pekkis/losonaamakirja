<?php

require_once __DIR__.'/../vendor/autoload.php';

use Knp\Provider\ConsoleServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

$app = new Silex\Application();

$app->register(new ConsoleServiceProvider(), array(
    'console.name'              => 'Losonaamakirja',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__.'/..'
));

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'losofacebook',
        'user' => 'root',
        'password' => 'g04753m135',
        'charset' => 'utf8',
    ),
));

return $app;
