<?php

/**
 * ownCloud - dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\dashboard\Service;

class GroupsService
{

    protected $userManager;

    public function __construct()
    {
        $this->userManager = $userManager;
    }

    /**
     * Returns a list of admin and normal groups
     * @param string $search
     * @return array
     */
    public function groups($search='')
    {
        $groupManager = \OC_Group::getManager();

        $isAdmin = \OC_User::isAdminUser(\OCP\User::getUser());

        $groupsInfo = new \OC\Group\MetaData(\OC_User::getUser(), $isAdmin, $groupManager);
        $groupsInfo->setSorting($groupsInfo::SORT_USERCOUNT);
        list($adminGroup, $groups) = $groupsInfo->get($search);

        return array(
            'adminGroups' => $adminGroup,
            'groups' => $groups,
        );
    }

    /**
     * Verify if group stats are enabled (see general settings screen, "Dashboard" section)
     * @return boolean
     */
    public static function isGroupsEnabled()
    {
        $appConfig = \OC::$server->getAppConfig();
        $result = $appConfig->getValue('dashboard', 'dashboard_groups_enabled', 'no');

        return $result;
    }

}
