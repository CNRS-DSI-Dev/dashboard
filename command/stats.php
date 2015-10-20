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
use OCA\Dashboard\Db\HistoryMapper;
use OCA\Dashboard\Db\History;
use OCA\Dashboard\Db\HistoryByGroupMapper;
use OCA\Dashboard\Db\HistoryByGroup;
use OCA\Dashboard\Service\StatService;
use OCA\Dashboard\Service\Variance;
use OCA\dashboard\lib\Helper;

class Stats extends Command {

    protected $statsTaskService;
    protected $historyMapper;
    protected $historyByGroupMapper;
    protected $path;
    protected $datas;
    protected $stats;

    public function __construct(StatService $statService, HistoryMapper $historyMapper,HistoryByGroupMapper $historyByGroupMapper)
    {
        $this->statsTaskService = $statsTaskService;
        $this->statService = $statService;
        $this->historyMapper = $historyMapper;
        $this->historyByGroupMapper = $historyByGroupMapper;

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
                . $prefix . 'dashboard_history_by_group (if needed) tables.')
            ->addOption('console', 'o', InputOption::VALUE_NONE, 'Show stats on console instead of storing them in database.');
    }

    /**
     * Run the command and display the result
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Beginning');

        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $datas = $this->historyMapper->countFrom($now);

        if (!$input->getOption('console')) {
            // If there's not already a history in database for today stats
            if (count($datas) <= 0) {
                $time_start = microtime(true);
                $this->stats = $this->getStats();
                $time_end = microtime(true);

                $time = $time_end - $time_start;
                $output->writeln('Stats extracted in ' . $time . "s.");

                $now = new \DateTime();
                $now = $now->format("Y-m-d H:i:s");

                $history = $this->createHistory($this->stats, $now);
                $this->historyMapper->insert($history);

                // stats by group ?
                if (Helper::isDashboardGroupsEnabled() and !empty($this->stats['groups'])) {
                    // One stat line per group for today
                    foreach($this->stats['groups'] as $groupName => $groupInfo) {
                        $historyByGroup = $this->createHistoryByGroup($groupName, $groupInfo, $now);
                        $this->historyByGroupMapper->insert($historyByGroup);
                    }
                }
            }
            else {
                $output->writeln('Dashboard stats already existing in database for today.');
            }
        }
        else {
            $time_start = microtime(true);
            $this->stats = $this->getStats();
            $time_end = microtime(true);

            $time = $time_end - $time_start;
            $output->writeln('Stats extracted in ' . $time . "s.");

            // Display on console instead of insert on database
            $this->displayStats($output);
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
     * Extract the global stats
     * @return array
     */
    protected function getStats()
    {
        $this->setPath($this->statService->getUserDataDir());
        $this->datas['users'] = $this->getUsers();
        $countUsers = count($this->datas['users']);

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
        $stats['filesPerUser'] = ($countUsers == 0) ? $stats['totalFiles'] : $stats['totalFiles'] / $countUsers;
        $stats['filesPerFolder'] = ($stats['totalFolders'] == 0) ? $stats['totalFiles']  : $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = ($countUsers == 0) ? $stats['totalFolders'] : $stats['totalFolders'] / $countUsers;
        $stats['sharesPerUser'] = ($countUsers == 0) ? $stats['totalShares'] : $stats['totalShares'] / $countUsers;
        $stats['sizePerUser'] = ($countUsers == 0) ? $stats['totalSize'] : $stats['totalSize'] / $countUsers;
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
     * @return \OCA\Dashboard\Db\History
     */
    protected function createHistory($globalStorageInfo, $when) {
        $history = new History;

        $history->setDate($when);
        $history->setNbUsers(count($this->datas['users']));
        $history->setDefaultQuota($globalStorageInfo['defaultQuota']);
        $history->setNbFolders($globalStorageInfo['totalFolders']);
        $history->setNbFiles($globalStorageInfo['totalFiles']);
        $history->setNbShares($globalStorageInfo['totalShares']);
        $history->setTotalUsedSpace($globalStorageInfo['totalSize']);
        $history->setSizePerUser($globalStorageInfo['sizePerUser']);
        $history->setFoldersPerUser($globalStorageInfo['foldersPerUser']);
        $history->setFilesPerUser($globalStorageInfo['filesPerUser']);
        $history->setSharesPerUser($globalStorageInfo['sharesPerUser']);
        $history->setSizePerFolder($globalStorageInfo['sizePerFolder']);
        $history->setFilesPerFolder($globalStorageInfo['filesPerFolder']);
        $history->setSizePerFile($globalStorageInfo['sizePerFile']);
        $history->setStdvFilesPerUser($globalStorageInfo['stdvNbFilesPerUser']);
        $history->setStdvFoldersPerUser($globalStorageInfo['stdvNbFoldersPerUser']);
        $history->setStdvSharesPerUser($globalStorageInfo['stdvNbSharesPerUser']);

        return $history;
    }

    /**
     * @param string $groupName Group gid
     * @param array $groupInfo Group stats
     * @param string $when datetime
     * @return \OCA\Dashboard\Db\HistoryByGroup
     */
    protected function createHistoryByGroup($groupName, $groupInfo, $when) {
        $historyByGroup = new HistoryByGroup;

        $historyByGroup->setGid($groupName);
        $historyByGroup->setDate($when);
        $historyByGroup->setTotalUsedSpace($groupInfo['filesize']);
        $historyByGroup->setNbUsers($groupInfo['nbUsers']);
        $historyByGroup->setNbFolders($groupInfo['nbFolders']);
        $historyByGroup->setNbFiles($groupInfo['nbFiles']);
        $historyByGroup->setNbShares($groupInfo['nbShares']);
        $historyByGroup->setSizePerUser($groupInfo['sizePerUser']);
        $historyByGroup->setFilesPerUser($groupInfo['filesPerUser']);
        $historyByGroup->setFoldersPerUser($groupInfo['foldersPerUser']);
        $historyByGroup->setSharesPerUser($groupInfo['sharesPerUser']);
        $historyByGroup->setSizePerFolder($groupInfo['sizePerFolder']);
        $historyByGroup->setFilesPerFolder($groupInfo['filesPerFolder']);
        $historyByGroup->setSizePerFile($groupInfo['sizePerFile']);

        return $historyByGroup;
    }

    /**
     * Displays stats on console
     * @param OutputInterface $output
     */
    protected function displayStats(OutputInterface $output)
    {
        // Display
        $output->writeln("Stats for " . $this->statService->getUserDataDir());
        $output->writeln("  defaultQuota   : " . \OC_Helper::humanFilesize($this->stats['defaultQuota']));
        $output->writeln("  nbFiles        : " . $this->stats['totalFiles'] . " for " . \OC_Helper::humanFilesize($this->stats['totalSize']));
        $output->writeln("  nbDirs         : " . $this->stats['totalFolders']);
        $output->writeln("  nbShares       : " . $this->stats['totalShares']);
        $output->writeln("  nbUsers        : ". count($this->datas['users']));
        $output->writeln("  filesPerUser   : ". $this->stats['filesPerUser']);
        $output->writeln("  filesPerFolder : ". $this->stats['filesPerFolder']);
        $output->writeln("  foldersPerUser : ". $this->stats['foldersPerUser']);
        $output->writeln("  sharesPerUser  : ". $this->stats['sharesPerUser']);
        $output->writeln("  sizePerUser    : ". \OC_Helper::humanFilesize($this->stats['sizePerUser']));
        $output->writeln("  sizePerFile    : ". \OC_Helper::humanFilesize($this->stats['sizePerFile']));
        $output->writeln("  sizePerFolder  : ". \OC_Helper::humanFilesize($this->stats['sizePerFolder']));
        $output->writeln("  stdvNbFilesPerUser   : ". $this->stats['stdvNbFilesPerUser']);
        $output->writeln("  stdvNbFoldersPerUser : ". $this->stats['stdvNbFoldersPerUser']);
        $output->writeln("  stdvNbSharesPerUser  : ". $this->stats['stdvNbSharesPerUser']);

        // stats by group ?
        if (Helper::isDashboardGroupsEnabled() and !empty($this->stats['groups'])) {
            // One stat line per group for today
            foreach($this->stats['groups'] as $groupName => $groupInfo) {
                $output->writeln("  GROUP : " . $groupName);
                $output->writeln("    nbUsers   : " . $groupInfo['nbUsers']);
                $output->writeln("    nbFiles   : " . $groupInfo['nbFiles']);
                $output->writeln("    nbFolders : " . $groupInfo['nbFolders']);
                $output->writeln("    filesize  : " . \OC_Helper::humanFilesize($groupInfo['filesize']));
            }
        }
    }
}
