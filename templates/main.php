<?php

\OCP\Util::addStyle('dashboard', 'dashboard');

?>

<div id="dashboard">
    Dashboard
</div>

<div>
    <p>There are <?php print_r($_['nbUsers']); ?> users.</p>
    <p>Global free space : <?= $_['globalFreeSpace'];?>.</p>
    <p>Global storage info : <pre><?php print_r($_['globalStorageInfo']);?></pre></p>
    <p>User data dir : <?= $_['userDataDir'];?></p>
</div>

<div id="footer">
    <p>You're user id #<?= $_['uid']; ?> (last log : <?= $_['userLastLogin']; ?>) - Dashboard version #<?= $_['appVersion']; ?></p>
</div>
