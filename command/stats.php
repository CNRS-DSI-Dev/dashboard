<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use OCA\Dashboard\Service\StatService;
use OCA\Dashboard\Service\Variance;
use OCA\dashboard\lib\Helper;

class Stats extends Command {

    protected $statsTaskService;
    protected $path;
    protected $countUsers;
    protected $datas;
    protected $stats;

    public function __construct(StatService $statService)
    {
        $this->statsTaskService = $statsTaskService;
        $this->statService = $statService;

        parent::__construct();
    }

    /**
     * Display help message
     */
    protected function configure()
    {
        $prefix = \OCP\Config::getSystemValue('dbtableprefix', 'oc_');

        $this
            ->setName('dashboard:stats')
            ->setDescription('Get realtime stats and insert them into '
                . $prefix . 'dashboard_history and '
                . $prefix . 'dashboard_history_by_group (if needed) tables.');
    }

    /**
     * Run the command and display the result
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Beginning');

        $time_start = microtime(true);
        $this->stats = $this->getStats();
        $time_end = microtime(true);

        $time = $time_end - $time_start;

        // Display
        $output->writeln("Stats for " . $this->statService->getUserDataDir());
        $output->writeln("  defaultQuota  : " . $this->humanFilesize($this->stats['defaultQuota']));
        $output->writeln("  nbFiles : " . $this->stats['totalFiles'] . " for " . $this->humanFilesize($this->stats['totalSize']));
        $output->writeln("  nbDirs  : " . $this->stats['totalFolders']);
        $output->writeln("  nbShares  : " . $this->stats['totalShares']);
        $output->writeln("  time needed to go through entire filetree : " . $time);
        $output->writeln("  nbUsers : ". count($this->datas['users']));
        $output->writeln("  filesPerUser : ". $this->stats['filesPerUser']);
        $output->writeln("  filesPerFolder : ". $this->stats['filesPerFolder']);
        $output->writeln("  foldersPerUser : ". $this->stats['foldersPerUser']);
        $output->writeln("  sharesPerUser : ". $this->stats['sharesPerUser']);
        $output->writeln("  sizePerUser : ". $this->stats['sizePerUser']);
        $output->writeln("  sizePerFile : ". $this->stats['sizePerFile']);
        $output->writeln("  sizePerFolder : ". $this->stats['sizePerFolder']);
        $output->writeln("  stdvNbFilesPerUser : ". $this->stats['stdvNbFilesPerUser']);
        $output->writeln("  stdvNbFoldersPerUser : ". $this->stats['stdvNbFoldersPerUser']);
        $output->writeln("  stdvNbSharesPerUser : ". $this->stats['stdvNbSharesPerUser']);

        // stats by group ?
        if (Helper::isDashboardGroupsEnabled() and !empty($this->stats['groups'])) {
            // One stat line per group for today
            foreach($this->stats['groups'] as $groupName => $groupInfo) {
                $output->writeln("  GROUP : " . $groupName);
                $output->writeln("    nbUsers : " . $groupInfo['nbUsers']);
                $output->writeln("    nbFiles : " . $groupInfo['nbFiles']);
                $output->writeln("    nbFolders : " . $groupInfo['nbFolders']);
            }
        }

        $output->writeln('Done');
    }

    /**
     * Setter for path
     * @param string $path Initial path for owncloud data directory
     */
    protected function setPath($path)
    {
        /* get the absolute path and ensure it has a trailing slash */
        $this->path = realpath($path);
        if (substr($this->path, -1) !== DIRECTORY_SEPARATOR) {
            $this->path .= DIRECTORY_SEPARATOR;
        }
    }

    /**
     * Go through the $this->path directory
     * @return [type] [description]
     */
    protected function getStats()
    {
        $this->setPath($this->statService->getUserDataDir());
        $this->datas['users'] = $this->getUsers();
        $this->countUsers = count($this->datas['users']);

        // initialize variances
        $nbFoldersVariance = new Variance;
        $nbFilesVariance = new Variance;
        $nbSharesVariance = new Variance;

        // initialize stats
        $stats = array();
        $stats['totalFiles'] = 0;
        $stats['totalFolders'] = 0;
        $stats['totalShares'] = 0;
        $stats['totalSize'] = 0;
        $stats['defaultQuota'] = \OCP\Util::computerFileSize(\OCP\Config::getAppValue('files', 'default_quota', 'none'));

        $statsByGroup = false;
        $statEnabledGroupList = array();
        // stat enabled groups list
        if (Helper::isDashboardGroupsEnabled()) {
            $statsByGroup = true;
            $statEnabledGroupList = Helper::getDashboardGroupList();
            $stats['groups'] = array();
        }

        // get stats for each user
        foreach($this->datas['users'] as $uid) {
            $userStats = array();
            $userStats['nbFiles'] = 0;
            $userStats['nbFolders'] = 0;
            $userStats['nbShares'] = 0;
            $userStats['filesize'] = 0;

            // group stats ?
            $groupList = array();
            if ($statsByGroup) {
                $userGroups = \OC_Group::getUserGroups($uid);
                $groupList = array_intersect($userGroups, $statEnabledGroupList);

                foreach($groupList as $group) {
                    if (!isset($stats['groups'][$group])) {
                        $stats['groups'][$group] = array();
                        $stats['groups'][$group]['nbUsers'] = 0;
                        $stats['groups'][$group]['nbFiles'] = 0;
                        $stats['groups'][$group]['nbFolders'] = 0;
                        $stats['groups'][$group]['nbShares'] = 0;
                        $stats['groups'][$group]['filesize'] = 0;
                    }
                    $stats['groups'][$group]['nbUsers']++;
                }
            }

            // really get the user stats !
            $this->getUserStats($this->path.$uid, $userStats);
            $userStats['nbShare'] = $this->getSharesStats($uid);

            // deviation cacul
            $nbFoldersVariance->addValue($userStats['nbFolders']);
            $nbFilesVariance->addValue($userStats['nbFiles']);
            $nbSharesVariance->addValue($userStats['nbShares']);

            // groups stats
            if ($statsByGroup) {
                foreach($groupList as $group) {
                    $stats['groups'][$group]['nbFiles'] += $userStats['nbFiles'];
                    $stats['groups'][$group]['nbFolders'] += $userStats['nbFolders'];
                    $stats['groups'][$group]['nbShares'] += $userStats['nbShares'];
                    $stats['groups'][$group]['filesize'] += $userStats['filesize'];
                }
            }

            $stats['totalFolders'] += $userStats['nbFolders'];
            $stats['totalFiles'] += $userStats['nbFiles'];
            $stats['totalSize'] += $userStats['filesize'];
            $stats['totalShares'] += $userStats['nbShare'];
        }

        // some basic stats
        $stats['filesPerUser'] = ($this->countUsers == 0) ? $stats['totalFiles'] : $stats['totalFiles'] / $this->countUsers;
        $stats['filesPerFolder'] = ($stats['totalFolders'] == 0) ? $stats['totalFiles']  : $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = ($this->countUsers == 0) ? $stats['totalFolders'] : $stats['totalFolders'] / $this->countUsers;
        $stats['sharesPerUser'] = ($this->countUsers == 0) ? $stats['totalShares'] : $stats['totalShares'] / $this->countUsers;
        $stats['sizePerUser'] = ($this->countUsers == 0) ? $stats['totalSize'] : $stats['totalSize'] / $this->countUsers;
        $stats['sizePerFile'] = ($stats['totalFiles'] == 0) ? $stats['totalSize'] : $stats['totalSize'] / $stats['totalFiles'];
        $stats['sizePerFolder'] = ($stats['totalFolders'] == 0) ?  $stats['totalSize'] : $stats['totalSize'] / $stats['totalFolders'];

        // by groups
        if ($statsByGroup) {
            foreach(array_keys($stats['groups']) as $group) {
                $stats['groups'][$group]['filesPerUser'] = ($stats['groups'][$group]['nbUsers'] == 0) ? $stats['groups'][$group]['nbFiles'] : $stats['groups'][$group]['nbFiles'] / $stats['groups'][$group]['nbUsers'];
                $stats['groups'][$group]['filesPerFolder'] = ($stats['groups'][$group]['nbFolders'] == 0) ? $stats['groups'][$group]['nbFiles'] : $stats['groups'][$group]['nbFiles'] / $stats['groups'][$group]['nbFolders'];
                $stats['groups'][$group]['foldersPerUser'] = ($stats['groups'][$group]['nbUsers'] == 0) ? $stats['groups'][$group]['nbFolders'] : $stats['groups'][$group]['nbFolders'] / $stats['groups'][$group]['nbUsers'];
                $stats['groups'][$group]['sharesPerUser'] = ($stats['groups'][$group]['nbUsers'] == 0) ? $stats['groups'][$group]['nbShares'] : $stats['groups'][$group]['nbShares'] / $stats['groups'][$group]['nbUsers'];
                $stats['groups'][$group]['sizePerUser'] = ($stats['groups'][$group]['nbUsers'] == 0) ? $stats['groups'][$group]['filesize'] : $stats['groups'][$group]['filesize'] / $stats['groups'][$group]['nbUsers'];
                $stats['groups'][$group]['sizePerFile'] = ($stats['groups'][$group]['nbFiles'] == 0) ? $stats['groups'][$group]['filesize'] : $stats['groups'][$group]['filesize'] / $stats['groups'][$group]['nbFiles'];
                $stats['groups'][$group]['sizePerFolder'] = ($stats['groups'][$group]['nbFolders'] == 0) ? $stats['groups'][$group]['filesize'] : $stats['groups'][$group]['filesize'] / $stats['groups'][$group]['nbFolders'];
            }
        }

        // variance
        //$stats['meanNbFilesPerUser'] = $nbFilesVariance->getMean();
        $stats['stdvNbFilesPerUser'] = $nbFilesVariance->getStandardDeviation();
        $stats['stdvNbFoldersPerUser'] = $nbFoldersVariance->getStandardDeviation();
        $stats['stdvNbSharesPerUser'] = $nbSharesVariance->getStandardDeviation();

        return $stats;
    }

    protected function getUserStats($path, &$userStats)
    {
        /* ensure the path has a trailing slash */
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }

        // Inspire by http://rosettacode.org/wiki/Walk_Directory_Tree#PHP_BFS_.28Breadth_First_Search.29
        $nbFiles = $nbDirs = $totalSize = 0;
        $level = 0;
        $queue = array($path => $level);
        clearstatcache();

        while(!empty($queue)) {
            /* get first element from the queue */
            // array_shift do not return the 'key', but only the 'value'...
            foreach($queue as $path => $level) {
                unset($queue[$path]);
                break;
            }

            $dh = @opendir($path);
            if (!$dh) continue;
            while(($filename = readdir($dh)) !== false) {
                /* dont recurse back up levels */
                if ($filename == '.' || $filename == '..')
                    continue;

                /* get the full path */
                $filename = $path . $filename;

                /* Don't follow symlinks */
                if (is_link($filename))
                    continue;

                if (is_dir($filename)) {
                    /* ensure the path has a trailing slash */
                    if (substr($filename, -1) !== DIRECTORY_SEPARATOR) {
                        $filename .= DIRECTORY_SEPARATOR;
                    }

                    /* check if we have already queued this path */
                    if (array_key_exists($filename, $queue))
                        continue;

                    $userStats['nbFolders']++;

                    /* queue directories for later search */
                    $queue = array($filename => $level + 1) + $queue;
                }
                else {
                    if ($level >= 1) {
                        $userStats['nbFiles']++;
                        $userStats['filesize'] += filesize($filename);
                    }
                }
            }
            closedir($dh);
        }
    }

    /**
     * Return list of user UID
     * @return array
     */
    protected function getUsers() {
        return \OCP\User::getUsers();
    }

    /**
     * Returns nb of share for a choosen user
     * @param  string $uid  UID of a user
     * @return integer
     */
    protected function getSharesStats($uid) {
        $sharedFiles   = \OC\Share\Share::getItems('file', null, null, null, $uid, \OC\Share\Share::FORMAT_NONE, null, -1, false);

        return count($sharedFiles);
    }

    /**
     * Return readable string of a big number of bits
     * @param  [type]  $bytes    [description]
     * @param  integer $decimals [description]
     * @return [type]            [description]
     */
    protected function humanFilesize($bytes, $decimals = 2)
    {
      $sz = 'BKMGTP';
      $factor = floor((strlen($bytes) - 1) / 3);
      return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }
}

function debug($var, $msg="", $lvl=0, $border=false) {
    $tabul = str_repeat("    ", $lvl) ; ;

    if ($border) {
        echo '<div style="background-color:#d99;text-align:left;margin:5px;padding:5px;color:black;border:3px solid red;">' ;
    }

    if (is_array($var)) {
        echo $tabul."$msg (array)\n" ;
        foreach($var as $key => $val) {
            debug($val, "[$key]", $lvl+1) ;
        }
    }
    elseif(is_object($var)) {
        $array = array() ;
        $array = (array)$var ;
        echo $tabul ."$msg (object ". get_class($var) .") \n" ;
        debug($array, "", $lvl+1) ;
    }
    elseif(is_bool($var)) {
        $boolean2string = ($var)?"TRUE":"FALSE" ;
        echo $tabul .$msg ." (boolean):". $boolean2string .":\n" ;
    }
    else {
        echo $tabul ."$msg (". gettype($var) ."):$var:\n" ;
    }

    if ($border) {
        echo "</div>" ;
    }
}
