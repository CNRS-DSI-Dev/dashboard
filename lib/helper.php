<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Lib;

class Helper
{
    /**
     * Verify if group stats are enabled (see general settings screen, "Dashboard" section)
     * @return boolean
     */
    public static function isDashboardGroupsEnabled()
    {
        $appConfig = \OC::$server->getAppConfig();
        $result = $appConfig->getValue('dashboard', 'dashboard_groups_enabled', 'no');
        return ($result === 'yes') ? true : false;
    }

}
