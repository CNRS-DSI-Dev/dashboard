<?php

namespace OCA\Dashboard\Db;

use \OCP\AppFramework\Db\Entity;

class History extends Entity {
    protected $date;
    protected $defaultQuota;
    protected $totalUsedSpace;
    protected $nbUsers;
    protected $nbFolders;
    protected $nbFiles;
    protected $nbShares;
    protected $sizePerUser;
    protected $filesPerUser;
    protected $foldersPerUser;
    protected $sharesPerUser;
    protected $sizePerFolder;
    protected $filesPerFolder;
    protected $sizePerFile;
    protected $stdvFilesPerUser;
    protected $stdvFoldersPerUser;
    protected $stdvSharesPerUser;

    public function __construct() {
        $this->addType('total_used_space', 'integer');
        $this->addType('nb_users', 'integer');
        $this->addType('nb_folders', 'integer');
        $this->addType('nb_files', 'integer');
        $this->addType('nb_shares', 'integer');
        $this->addType('size_per_user', 'float');
        $this->addType('folders_per_user', 'float');
        $this->addType('files_per_user', 'float');
        $this->addType('shares_per_user', 'float');
        $this->addType('size_per_folder', 'float');
        $this->addType('files_per_folder', 'float');
        $this->addType('size_per_file', 'float');
        $this->addType('stdvFilesPeruser', 'float');
        $this->addType('stdvFoldersPerUser', 'float');
        $this->addType('stdvSharesPerUser', 'float');
    }
}