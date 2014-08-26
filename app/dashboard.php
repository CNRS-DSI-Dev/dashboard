<?php

namespace OCA\Dashboard\App;

use \OCP\AppFramework\App;
use \OCA\Dashboard\Controller\PageController;
use \OCA\Dashboard\Controller\APIStatsController;
use \OCA\Dashboard\Controller\StatService;

class Dashboard extends App {


    /**
     * Define your dependencies in here
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('dashboard', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('PageController', function($c){
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('StatService')
            );
        });

        $container->registerService('ApiStatsController', function($c){
            return new APIStatsController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('StatService')
            );
        });

        $container->registerService('StatService', function($c){
            return new StatService(
                $c->query('UserManager'),
                $c->query('RootStorage')
            );
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });

        $container->registerService('RootStorage', function($c) {
            return $c->query('ServerContainer')->getRootFolder();
        });

        /**
         * Core
         */
        $container->registerService('UserId', function($c) {
            return \OCP\User::getUser();
        });

        $container->registerService('L10N', function($c) {
            return \OC_L10N::get($c['AppName']);
        });

        $container->registerService('CoreConfig', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });

    }


}