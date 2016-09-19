<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\Util::addStyle('dashboard', 'settings-admin');

\OCP\Util::addScript('dashboard', 'lib/angular.min');
\OCP\Util::addScript('dashboard', 'lib/angucomplete-alt');
\OCP\Util::addScript('dashboard', 'app/settings-admin');
\OCP\Util::addScript('dashboard', 'app/services/dashboard.services');

?>
<div id="dashboard" class="section" ng-controller="groupsController">
    <h2><?php p($l->t('Dashboard')); ?></h2>

    <p>
        <input type="checkbox" ng-model="dashboardGroupsEnabled"
            ng-true-value="yes" ng-false-value="no" ng-change="storeChoice()">
        <label for="dashboardGroupsEnabled"><?php p($l->t('Allow to get and store stats for some groups'));?></label>
    </p>

    <div id="dashboardGroups" class="indent" ng-show="dashboardGroupsEnabled" ng-cloak class="ng-cloak">

        <h3><?php p($l->t('List of groups')); ?><p>

        <div id="searchGroup">
            <angucomplete-alt id="groups"
                placeholder="{{ searchPlaceholder }}"
                pause="400"
                selected-object="addGroup"
                remote-url="{{ dashboardGroupsUrl }}"
                remote-url-data-field="groups"
                minlength = "1"
                title-field="name"
                clear-selected="true"></angucomplete-alt>

            <span class="utils">
                <a href="#" class="action delete" original-title="<?php p($l->t('Delete'))?>" ng-click="deleteGroup()">
                    <img src="<?php print_unescaped(image_path('core', 'actions/delete.svg')) ?>" class="svg" />
                </a>
            </span>

        </div>

        <div id="groupList">
            <span class="groupItem" ng-repeat="group in groupList | orderBy:'name'">
                <span ng-click="removeGroup(group.id)" title="<?php p($l->t('Remove this group'));?>">[X]</span> {{ group.name }}
            </span>
        </div>

    </div>
</div>

