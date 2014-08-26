<?php
namespace OCA\HelloWorld\Cron;

use \OCA\HelloWorld\App\HelloWorld;

class SayHello {

    public static function run() {
        $app = new HelloWorld();
        $container = $app->getContainer();
        $container->query('SayHelloService');
    }

}