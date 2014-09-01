<?php
namespace OCA\Dashboard\Cron;

use \OCA\Dashboard\App\Dashboard;

class StatsTask {

    public static function run() {
        $app = new Dashboard();
        $container = $app->getContainer();
        $container->query('StatsTaskService')->run();
    }

}