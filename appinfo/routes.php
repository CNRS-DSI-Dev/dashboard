<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard;

use \OCA\Dashboard\App\Dashboard;

$application = new Dashboard();
$application->registerRoutes($this, array(
    'routes' => array(
        array(
            'name' => 'page#index',
            'url' => '/',
            'verb' => 'GET',
        ),
        array(
            'name' => 'api_stats#stats',
            'url' => '/api/1.0/stats.{format}',
            'defaults' => array('format' => 'json'),
            'verb' => 'GET',
        ),
        array(
            'name' => 'api_stats#index',
            'url' => '/api/1.0/index.{format}',
            'defaults' => array('format' => 'json'),
            'verb' => 'GET',
        ),
        array(
            'name' => 'api_stats#history_stats',
            'url' => '/api/1.0/history_stats/{format}/{dataType}/{range}/{wanthumanreadable}',
            'defaults' => array('format' => 'json', 'dataType' => 'all', 'range' => 30, 'wanthumanreadable' => 1),
            'verb' => 'GET',
        ),
        array(
            'name' => 'api_stats#preflighted_cors',
            'url' => '/api/1.0/stats/{path}',
            'verb' => 'OPTIONS',
            'requirements' => array('path' => '.+'),
        ),
        array(
            'name' => 'api_groups#is_groups_enabled',
            'url' => '/api/1.0/is_groups_enabled',
            'verb' => 'GET',
        ),
        array(
            'name' => 'api_groups#groups',
            'url' => '/api/1.0/groups/{search}',
            'default' => array('search' => ''),
            'verb' => 'GET',
        ),
    ),
));
