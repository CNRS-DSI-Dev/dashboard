<?php
namespace OCA\Dashboard\Controller;

class StatService {

    protected $userManager;
    protected $rootStorage;
    protected $datas;

    public function __construct($userManager, $rootStorage) {
        $this->userManager = $userManager;
        $this->rootStorage = $rootStorage;

        $this->datas = array();
    }

    public function countUsers() {
        if (isset($this->datas['nbUsers'])) {
            return $this->datas['nbUsers'];
        }

        $nbUsers = 0;

        $nbUsersByBackend = $this->userManager->countUsers();

        if (!empty($nbUsersByBackend) and is_array($nbUsersByBackend)) {
            foreach($nbUsersByBackend as $backend => $count) {
                $nbUsers += $count;
            }
        }

        $this->datas['nbUsers'] = $nbUsers;

        return $nbUsers;
    }

    public function globalFreeSpace() {
        $fs = \OCP\Files::getStorage('files');
        return $fs->free_space();
    }

    public function getUserDataDir() {
        return \OCP\Config::getSystemValue('datadirectory', '');
    }

    public function getGlobalStorageInfo() {
        $view = new \OC\Files\View();
        $stats = array();
        $stats['totalFiles'] = 0;
        $stats['totalFolders'] = 0;
        $stats['totalShares'] = 0;
        $stats['totalSize'] = 0;
        $this->getFilesStat($view, '', $stats);

        // some basic stats
        $stats['filesPerUser'] = $stats['totalFiles'] / $this->countUsers();
        $stats['filesPerFolder'] = $stats['totalFiles'] / $stats['totalFolders'];
        $stats['foldersPerUser'] = $stats['totalFolders'] / $this->countUsers();
        $stats['sharesPerUser'] = $stats['totalShares'] / $this->countUsers();
        $stats['sizePerUser'] = $stats['totalSize'] / $this->countUsers();
        $stats['sizePerFile'] = $stats['totalSize'] / $stats['totalFiles'];
        $stats['sizePerFolders'] = $stats['totalSize'] / $stats['totalFolders'];

        // TODO : variance

        return $stats;
    }

    /**
     * Get some global stats
     * @param \OC\Files\View $view
     * @param string $path the path
     * @param mixed $stats array to store the extrated stats
     */
    protected function getFilesStat($view, $path='', &$stats) {
        $dc = $view->getDirectoryContent($path);
        foreach($dc as $item) {
            // FIXME : preg the first part of filepath to get owner...
            $owner = $this->getOwner($item->getPath());

            // $owner = $view->getOwner($item->getPath());
            if (!isset($stats[$owner])) {
                if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                    $stats[$owner] = array();
                    // $stats[$owner]['entries'] = array();
                    $stats[$owner]['nbFiles'] = 0;
                    $stats[$owner]['nbFolders'] = 0;
                    $stats[$owner]['nbShares'] = 0;
                    $stats[$owner]['filesize'] = 0;
                }
                else {
                    // do not get files in rootDir
                    continue;
                }
            }

            if ($item->isShared()) {
                $stats[$owner]['nbShares']++;
                $stats['totalShares']++;
                continue;
            }

            // array_push($stats[$owner]['entries'], $item->getPath());

            // if folder, recurse
            if ($item->getType() == \OCP\Files\FileInfo::TYPE_FOLDER) {
                $stats[$owner]['nbFolders']++;
                $stats['totalFolders']++;
                $this->getFilesStat($view, $item->getPath(), $stats);
            }
            else {
                $stats[$owner]['nbFiles']++;
                $stats['totalFiles']++;
                $stats[$owner]['filesize'] += $item->getSize();
                $stats['totalSize'] += $item->getSize();
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

}