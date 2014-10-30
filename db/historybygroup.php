<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Db;

use \OCP\AppFramework\Db\Entity;

class HistoryByGroup extends Entity {
    protected $date;
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
    }
}
