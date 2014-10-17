<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\App;

use \OCP\AppFramework\App;
use \OCA\Dashboard\Controller\PageController;
use \OCA\Dashboard\Controller\APIStatsController;
use \OCA\Dashboard\Controller\APIGroupsController;
use \OCA\Dashboard\Service\StatService;
use \OCA\Dashboard\Service\HistoryService;
use \OCA\Dashboard\Service\StatsTaskService;
use \OCA\Dashboard\Service\GroupsService;
use \OCA\Dashboard\Db\HistoryMapper;

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
                $c->query('L10N')
            );
        });

        $container->registerService('ApiStatsController', function($c){
            return new APIStatsController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('StatService'),
                $c->query('HistoryService')
            );
        });

        $container->registerService('ApiGroupsController', function($c){
            return new APIGroupsController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('CoreConfig'),
                $c->query('UserId'),
                $c->query('GroupsService')
            );
        });

        /**
         * Services
         */

        $container->registerService('StatService', function($c){
            return new StatService(
                $c->query('UserManager'),
                $c->query('RootStorage')
            );
        });

        $container->registerService('HistoryService', function($c){
            return new HistoryService(
                $c->query('HistoryMapper')
            );
        });

        $container->registerService('StatsTaskService', function($c) {
            return new StatsTaskService(
                $c->query('StatService'),
                $c->query('HistoryMapper')
            );
        });

        $container->registerService('GroupsService', function($c){
            return new GroupsService(
                $c->query('UserManager')
            );
        });

        $container->registerService('UserManager', function($c) {
            return $c->query('ServerContainer')->getUserManager();
        });

        /**
         * Database Layer
         */
        $container->registerService('HistoryMapper', function($c) {
            return new HistoryMapper(
                $c->query('ServerContainer')->getDb()
            );
        });

        /**
         * Storage Layer
         */
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
            return $c->query('ServerContainer')->getL10N($c->query('AppName'));
        });

        $container->registerService('CoreConfig', function($c) {
            return $c->query('ServerContainer')->getConfig();
        });

    }


}
