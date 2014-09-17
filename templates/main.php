<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

\OCP\Util::addStyle('dashboard', 'dashboard');

\OCP\Util::addScript('dashboard', 'lib/angular.min');
\OCP\Util::addScript('dashboard', 'lib/Chart.min');
\OCP\Util::addScript('dashboard', 'app/directives/chartjs-directive');
\OCP\Util::addScript('dashboard', 'app/services/dashboard.services');
\OCP\Util::addScript('dashboard', 'app/dashboard');

?>

<div ng-app="dashboard" ng-controller="statsController">

<div id="dashboard">
    Dashboard
</div>

<div id="container">

<div id="space" class="dataBlock">
    <p class="header"><?php p($l->t('Disk space')); ?> <span>{{ stats.history.totalUsedSpace | humanFileSize }}</span></p>
    <p><?php p($l->t('User data dir')); ?>: {{ stats.userDataDir }} </p>
    <p><?php p($l->t('Default quota per user')); ?>: {{ stats.history.defaultQuota | humanFileSize }}</p>
</div>

<div id="users" class="dataBlock">
    <p class="header"><?php p($l->t('Users')); ?> <span>{{ stats.history.nbUsers }}</span></p>
    <p><?php p($l->t('Size / user')); ?>: <span>{{ stats.history.sizePerUser | humanFileSize }}</span></p>
    <p><?php p($l->t('Files / user')); ?>: <span>{{ stats.history.filesPerUser | number:2 }} (<?php p($l->t('standard deviation')); ?>: {{ stats.history.stdvFilesPerUser | number:2 }})</span></p>
    <p><?php p($l->t('Folders / user')); ?>: {{ stats.history.foldersPerUser | number:2 }} (<?php p($l->t('standard deviation')); ?>: {{ stats.history.stdvFoldersPerUser | number:2 }})</span></p>
</div>

<div id="folders" class="dataBlock">
    <p class="header"><?php p($l->t('Folders')); ?> <span>{{ stats.history.nbFolders }}</span></p>
    <p><?php p($l->t('Size / folder')); ?>: <span>{{ stats.history.sizePerFolder | humanFileSize }}</span></p>
    <p><?php p($l->t('Files / folder')); ?>: <span>{{ stats.history.filesPerFolder | number:2 }}</span></p>
    <br>
</div>

<div id="files" class="dataBlock">
    <p class="header"><?php p($l->t('Files')); ?> <span>{{ stats.history.nbFiles }}</span></p>
    <p><?php p($l->t('Size / file')); ?>: <span>{{ stats.history.sizePerFile | humanFileSize }}</span></p>
    <br>
    <br>
</div>

<div id="shares" class="dataBlock">
    <p class="header"><?php p($l->t('Shares')); ?> <span>{{ stats.history.nbShares }}</span></p>
    <p><?php p($l->t('Shares / user')); ?>: <span>{{ stats.history.sharesPerUser | number:2 }}</span></p>
    <p><?php p($l->t('Standard deviation')); ?>: {{ stats.history.stdvFilesPerUser | number:2 }}</p>
    <br>
</div>

<div class="history">
    <div>
        <select ng-model="dataType" ng-options="type for type in dataTypes"></select>
        <select ng-model="nbDays" ng-options="choices as choices.label for choices in nbDaysChoices"></select>
    </div>
    <chart value="dataHistory" type="Line" width="800" height="300"></chart>
</div>

</div>

<div id="footer">
    <p>You're user id #{{ stats.uid }} (last log : {{ stats.userLastLogin }}) - Dashboard version #{{ stats.appVersion }}</p>
    <p>Icons provided by <a href="http://glyphicons.com/">GLYPHICONS.com</a>, released under <a href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported (CC BY 3.0)</a></p>
</div>

</div>