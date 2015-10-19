<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

use OCA\Dashboard\App\Dashboard;

$app = new Dashboard;
$c = $app->getContainer();
$statService = $c->query('StatService');

$application->add(new OCA\Dashboard\Command\Populate);
$application->add(new OCA\Dashboard\Command\Stats($statService));
