<?php
namespace OCA\Dashboard\Service;

class StatService {

    protected $userManager;
    protected $datas;

    public function __construct($userManager) {
        $this->userManager = $userManager;

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

        $dataRoot = $this->getUserDataDir() . '/';

        // 'users' is a temporary container, won't be send back
        $stats['users'] = array();
        $users = \OCP\User::getUsers();
        foreach ($users as $uid) {
            $userRoot = \OC_User::getHome($uid);
            $userDirectory = str_replace($dataRoot, '', $userRoot) . '/files';

            $stats['users'][$uid] = array();
            $stats['users'][$uid]['nbFiles'] = 0;
            $stats['users'][$uid]['nbFolders'] = 0;
            $stats['users'][$uid]['nbShares'] = 0;
            $stats['users'][$uid]['filesize'] = 0;
            //$stats['users'][$uid]['quota'] = \OC_Util::getUserQuota($uid);

            // extract datas
            $this->getFilesStat($view, $userDirectory, $stats['users'][$uid]);

            // files stats
            $stats['totalFolders'] += $stats['users'][$uid]['nbFolders'];
            $stats['totalFiles'] += $stats['users'][$uid]['nbFiles'];
            $stats['totalSize'] += $stats['users'][$uid]['filesize'];

            // shares
            $stats['users'][$uid]['nbShares'] = $this->getSharesStats($uid);
            $stats['totalShares'] += $stats['users'][$uid]['nbShares'];

            // variance evolutions
            $nbFoldersVariance->addValue($stats['users'][$uid]['nbFolders']);
            $nbFilesVariance->addValue($stats['users'][$uid]['nbFiles']);
            $nbSharesVariance->addValue($stats['users'][$uid]['nbShares']);
        }

        // some basic stats
        $stats['filesPerUser'] = $stats['totalFiles'] / $this->countUsers();
        $stats['filesPerFolder'] = $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = $stats['totalFolders'] / $this->countUsers();
        $stats['sharesPerUser'] = $stats['totalShares'] / $this->countUsers();
        $stats['sizePerUser'] = $stats['totalSize'] / $this->countUsers();
        $stats['sizePerFile'] = $stats['totalSize'] / $stats['totalFiles'];
        $stats['sizePerFolder'] = $stats['totalSize'] / $stats['totalFolders'];
        $stats['sharesPerUser'] = $stats['totalShares'] / $this->countUsers();

        // variance
        //$stats['meanNbFilesPerUser'] = $nbFilesVariance->getMean();
        $stats['stdvNbFilesPerUser'] = $nbFilesVariance->getStandardDeviation();
        $stats['stdvNbFoldersPerUser'] = $nbFoldersVariance->getStandardDeviation();
        $stats['stdvNbsharesPerUser'] = $nbSharesVariance->getStandardDeviation();

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
