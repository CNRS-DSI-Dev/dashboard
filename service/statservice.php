<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Service;

use OCA\Dashboard\Lib\Helper;

class PseudoUser
{
    protected $uid;

    function __construct($uid)
    {
        $this->uid = $uid;
    }

    function getUID($uid)
    {
        return $this->uid;
    }
}

class StatService
{

    protected $userManager;
    protected $rootStorage;
    protected $datas;

    public function __construct($userManager, $rootStorage) {
        $this->userManager = $userManager;
        $this->rootStorage = $rootStorage;

        $this->datas = array();
    }

    public function getUserDataDir() {
        if (!isset($this->datas['dataDirectory'])) {
            $this->datas['dataDirectory'] =  \OCP\Config::getSystemValue('datadirectory', \OC::$SERVERROOT.'/data');
        }
        return $this->datas['dataDirectory'];
    }

    public function countUsers() {
        if (!isset($this->datas['nbUsers'])) {
            $nbUsers = 0;

            $nbUsersByBackend = $this->userManager->countUsers();

            if (!empty($nbUsersByBackend) and is_array($nbUsersByBackend)) {
                foreach($nbUsersByBackend as $backend => $count) {
                    $nbUsers += $count;
                }
            }

            $this->datas['nbUsers'] = $nbUsers;
        }

        return $this->datas['nbUsers'];
    }

    /**
     * Get some global usage stats (file nb, user nb, ...)
     * @param string $gid Id of the group of users from which you want stats (means "all users" if $gid = '')
     */
    public function getGlobalStorageInfo() {
        $view = new \OC\Files\View();
        $stats = array();
        $stats['totalFiles'] = 0;
        $stats['totalFolders'] = 0;
        $stats['totalShares'] = 0;
        $stats['totalSize'] = 0;
        $stats['defaultQuota'] = \OCP\Util::computerFileSize(\OCP\Config::getAppValue('files', 'default_quota', 'none'));

        // initialize variances
        $nbFoldersVariance = new Variance;
        $nbFilesVariance = new Variance;
        $nbSharesVariance = new Variance;

        $dataRoot = $this->getUserDataDir();

        // user list
        $users = \OCP\User::getUsers();

        $statsByGroup = false;
        // stat enabled groups list
        if (Helper::isDashboardGroupsEnabled()) {
            $statsByGroup = true;
            $statEnabledGroupList = Helper::getDashboardGroupList();
            $stats['groups'] = array();
        }

        // 'users' is a temporary container, won't be send back
        // $stats['users'] = array();

        foreach ($users as $uid) {
            //$userDirectory = $this->rootStorage . '/' . $uid . '/files';
            $userDirectory = '/' . $uid . '/files';

            if (!is_readable($dataRoot . $userDirectory)) {
                continue;
            }

            // $stats['users'][$uid] = array();
            // $stats['users'][$uid]['nbFiles'] = 0;
            // $stats['users'][$uid]['nbFolders'] = 0;
            // $stats['users'][$uid]['nbShares'] = 0;
            // $stats['users'][$uid]['filesize'] = 0;
            $user = array();
            $user['nbFiles'] = 0;
            $user['nbFolders'] = 0;
            $user['nbShares'] = 0;
            $user['filesize'] = 0;

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

            // extract datas
            // $this->getFilesStat($view, $userDirectory, $stats['users'][$uid]);
            $this->getFilesStat($view, $userDirectory, $user);

            // files stats
            // $stats['totalFolders'] += $stats['users'][$uid]['nbFolders'];
            $stats['totalFolders'] += $user['nbFolders'];
            // $stats['totalFiles'] += $stats['users'][$uid]['nbFiles'];
            $stats['totalFiles'] += $user['nbFiles'];
            // $stats['totalSize'] += $stats['users'][$uid]['filesize'];
            $stats['totalSize'] += $user['filesize'];

            // shares
            $stats['users'][$uid]['nbShares'] = $this->getSharesStats($uid);
            // $stats['totalShares'] += $stats['users'][$uid]['nbShares'];
            $stats['totalShares'] += $user['nbShares'];

            // variance evolutions
            // $nbFoldersVariance->addValue($stats['users'][$uid]['nbFolders']);
            $nbFoldersVariance->addValue($user['nbFolders']);
            // $nbFilesVariance->addValue($stats['users'][$uid]['nbFiles']);
            $nbFilesVariance->addValue($user['nbFiles']);
            // $nbSharesVariance->addValue($stats['users'][$uid]['nbShares']);
            $nbSharesVariance->addValue($user['nbShares']);

            // groups stats
            if ($statsByGroup) {
                foreach($groupList as $group) {
                    // $stats['groups'][$group]['nbFiles'] += $stats['users'][$uid]['nbFiles'];
                    $stats['groups'][$group]['nbFiles'] += $user['nbFiles'];
                    // $stats['groups'][$group]['nbFolders'] += $stats['users'][$uid]['nbFolders'];
                    $stats['groups'][$group]['nbFolders'] += $user['nbFolders'];
                    // $stats['groups'][$group]['nbShares'] += $stats['users'][$uid]['nbShares'];
                    $stats['groups'][$group]['nbShares'] += $user['nbShares'];
                    // $stats['groups'][$group]['filesize'] += $stats['users'][$uid]['filesize'];
                    $stats['groups'][$group]['filesize'] += $user['filesize'];
                }
            }
        }

        // some basic stats
        $stats['filesPerUser'] = ($this->countUsers() == 0) ? $stats['totalFiles'] : $stats['totalFiles'] / $this->countUsers();
        $stats['filesPerFolder'] = ($stats['totalFolders'] == 0) ? $stats['totalFiles']  : $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = ($this->countUsers() == 0) ? $stats['totalFolders'] : $stats['totalFolders'] / $this->countUsers();
        $stats['sharesPerUser'] = ($this->countUsers() == 0) ? $stats['totalShares'] : $stats['totalShares'] / $this->countUsers();
        $stats['sizePerUser'] = ($this->countUsers() == 0) ? $stats['totalSize'] : $stats['totalSize'] / $this->countUsers();
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

        // don't send back 'users' details
        //unset($stats['users']);

        return $stats;
    }

    /**
     * Get some user informations on files and folders
     * @param \OC\Files\View $view
     * @param string $path the path
     * @param mixed $datas array to store the extrated infos
     */
    protected function getFilesStat($view, $path='', &$datas) {
        $dc = $view->getDirectoryContent($path);

        foreach($dc as $item) {
            if ($item->isShared()) {
                continue;
            }

            // if folder, recurse
            if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                $datas['nbFolders']++;
                $this->getFilesStat($view, $item->getPath(), $datas);
            }
            else {
                $datas['nbFiles']++;
                $datas['filesize'] += $item->getSize();
            }
        }
    }

    /**
     * Dirty function to extract owner from filepath
     * @param string $path
     * @return string owner of this filepath
     */
    protected function getOwner($path) {
        // admin files seem to begin with "//"
        if (strpos($path, "//") === 0) {
            return str_replace("//", "", $path);
        }

        preg_match("#^/([^/]*)/.*$#", $path, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }

        return '';
    }

    protected function getSharesStats($owner) {
        // $shares = \OCP\Share::getItemsSharedWithUser('file', 'admin');
        $sharedFiles   = \OC\Share\Share::getItems('file', null, null, null, $owner, \OC\Share\Share::FORMAT_NONE, null, -1, false);
// $f = fopen('/tmp/truc.log', 'a');
// fputs($f, $owner . " : files\n");
// fputs($f, print_r($sharedFiles, true) . "\n");
        //  $sharedFolders = \OC\Share\Share::getItems('folder', null, null, null, $owner, \OC\Share\Share::FORMAT_NONE, null, -1, false);
// fputs($f, $owner . " : folders\n");
// fputs($f, print_r($sharedFolders, true) . "\n");
// fclose($f);

        return count($sharedFiles)/* + count($sharedFolders)*/;
    }
}
