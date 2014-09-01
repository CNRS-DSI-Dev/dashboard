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

    public function __construct() {
        $this->addType('total_used_space', 'integer');
        $this->addType('nb_users', 'integer');
        $this->addType('nb_folders', 'integer');
        $this->addType('nb_files', 'integer');
        $this->addType('nb_shares', 'integer');
    }
}