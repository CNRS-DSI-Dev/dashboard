<?php

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
    <p class="header">Disk space <span>{{ stats.history.totalUsedSpace | humanFileSize }}</span></p>
    <p>User data dir : {{ stats.userDataDir }} </p>
    <p>Default quota per user : {{ stats.history.defaultQuota | humanFileSize }}</p>
</div>

<div id="users" class="dataBlock">
    <p class="header">Users <span>{{ stats.history.nbUsers }}</span></p>
    <p>Size / user <span>{{ stats.history.sizePerUser | humanFileSize }}</span></p>
    <p>Files / user <span>{{ stats.history.filesPerUser | number:2 }} (standard deviation: {{ stats.history.stdvFilesPerUser | number:2 }})</span></p>
    <p>Folders / user {{ stats.history.foldersPerUser | number:2 }} (standard deviation: {{ stats.history.stdvFoldersPerUser | number:2 }})</span></p>
</div>

<div id="folders" class="dataBlock">
    <p class="header">Folders <span>{{ stats.history.totalFolders }}</span></p>
    <p>Size / folder <span>{{ stats.history.sizePerFolder | humanFileSize }}</span></p>
    <p>Files / folder <span>{{ stats.history.filesPerFolder | number:2 }}</span></p>
    <br>
</div>

<div id="files" class="dataBlock">
    <p class="header">Files <span>{{ stats.history.nbFiles }}</span></p>
    <p>Size / file <span>{{ stats.history.sizePerFile | humanFileSize }}</span></p>
    <br>
    <br>
</div>

<div id="shares" class="dataBlock">
    <p class="header">Shares <span>{{ stats.history.totalShares }}</span></p>
    <p>Shares / user <span>{{ stats.history.sharesPerUser | number:2 }}</span></p>
    <p>Standard deviation: {{ stats.history.stdvFilesPerUser | number:2 }}</p>
    <br>
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
</div>

</div>