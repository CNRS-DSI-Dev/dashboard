<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\Util::addScript('dashboard', 'lib/angular');
\OCP\Util::addScript('dashboard', 'lib/angucomplete-alt');
\OCP\Util::addScript('dashboard', 'settings-admin');

\OCP\Util::addStyle('dashboard', 'settings-admin');

?>
<div id="dashboard" class="section" ng-controller="groupsController">
    <h2><?php p($l->t('Dashboard')); ?></h2>

    <p>
        <input type="checkbox" name="dashboardgroups_enabled" id="dashboardGroupsEnabled"
           value="1" <?php if ($_['dashboardGroupsEnabled']) print_unescaped('checked="checked"'); ?> />
        <label for="dashboardGroupsEnabled"><?php p($l->t('Allow to get and store stats for some groups.'));?></label>
    </p>

    <div id="dashboardGroups" class="indent <?php if (!$_['dashboardGroupsEnabled'] || $_['dashboardGroupsEnabled'] === 'no') p('hidden'); ?>">

        <h3>List of groups.<p>

        <div>
            <p>{{ groupList | json }}</p>
        </div>

        <div id="searchGroup">
            <angucomplete-alt id="groups"
                placeholder="{{ searchPlaceholder }}"
                pause="400"
                selected-object="addGroup"
                remote-url="{{ lotsofgroupsGroupsUrl }}"
                remote-url-data-field="groups"
                minlength = "1"
                title-field="name" ></angucomplete-alt>

            <?php if (\OC_User::isAdminUser(\OCP\User::getUser())) { ?>
            <span class="utils">
                <a href="#" class="action delete" original-title="<?php p($l->t('Delete'))?>" ng-click="deleteGroup()">
                    <img src="<?php print_unescaped(image_path('core', 'actions/delete.svg')) ?>" class="svg" />
                </a>
            </span>
            <?php } ?>

        </div>

    </div>
</div>

