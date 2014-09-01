<?php

\OCP\Util::addStyle('dashboard', 'dashboard');
\OCP\Util::addScript('dashboard', 'angular.min');
\OCP\Util::addScript('dashboard', 'dashboard');

?>

<div ng-app="dashboard" ng-controller="statsController">

<div id="dashboard">
    Dashboard
</div>

<div id="container">

<div id="space" class="dataBlock">
    <p class="header">Disk space</p>
    <p>User data dir : {{ stats.userDataDir }} </p>
    <p>Default quota per user : {{ stats.globalStorageInfo.defaultQuota | humanFileSize }}</p>
    <p>Total used space : {{ stats.globalStorageInfo.totalSize | humanFileSize }}</p>
</div>

<div id="users" class="dataBlock">
    <p class="header">Users <span>{{ stats.nbUsers }}</span></p>
    <p>Size / user <span>{{ stats.globalStorageInfo.sizePerUser | humanFileSize }}</span></p>
    <p>Files / user <span>{{ stats.globalStorageInfo.filesPerUser | number:2 }}</span></p>
    <p>Folders / user {{ stats.globalStorageInfo.foldersPerUser | number:2 }}</span></p>
</div>

<div id="folders" class="dataBlock">
    <p class="header">Folders <span>{{ stats.globalStorageInfo.totalFolders }} <?php p($_['globalStorageInfo']['totalFolders']);?></span></p>
    <p>Size / folder <span>{{ stats.globalStorageInfo.sizePerFolder | humanFileSize }}</span></p>
    <p>Files / folder <span>{{ stats.globalStorageInfo.filesPerFolder | number:2 }}</span></p>
    <br>
</div>

<div id="files" class="dataBlock">
    <p class="header">Files <span>{{ stats.globalStorageInfo.totalFiles }}</span></p>
    <p>Size / file <span>{{ stats.globalStorageInfo.sizePerFile | humanFileSize }}</span></p>
    <br>
    <br>
</div>

<div id="shares" class="dataBlock">
    <p class="header">Shares <span>{{ stats.globalStorageInfo.totalShares }}</span></p>
    <p>Shares / user <span>{{ stats.globalStorageInfo.sharesPerUser | number:2 }}</span></p>
    <br>
    <br>
</div>

</div>

<div id="footer">
    <p>You're user id #{{ stats.uid }} (last log : {{ stats.userLastLogin }}) - Dashboard version #{{ stats.appVersion }}</p>
</div>

</div>