<?php
/**
 * ownCloud - dashboard
 *
 * @author Patrick Paysant
 * @copyright 2014 DSI CNRS https://www.dsi.cnrs.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
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
