<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\App::addNavigationEntry(array(
    'id' => 'dashboard',
    'order' => 10,
    'href' => \OCP\Util::linkToRoute('dashboard.page.index'),
    'icon' => \OCP\Util::imagePath('dashboard', 'dashboard.svg'),
    'name' => \OC_L10N::get('dashboard')->t('Dashboard')
));

// cron task
\OCP\Backgroundjob::addRegularTask('\OCA\dashboard\Cron\statsTask', 'run');
