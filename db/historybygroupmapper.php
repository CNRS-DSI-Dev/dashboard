<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Db;

use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;

class HistoryByGroupMapper extends Mapper {
    public function __construct(IDb $db) {
        parent::__construct($db, 'dashboard_history_by_group');
    }

    public function findAll($limit=null, $offset=null) {
        $sql = "SELECT * FROM *PREFIX*dashboard_history_by_group";
        return $this->findEntities($sql, $limit, $offset);
    }

    public function countFrom($datetime) {
        $sql = "SELECT id FROM *PREFIX*dashboard_history_by_group WHERE date > ? ORDER BY date";
        return $this->findEntities($sql, array(
            $datetime->format('Y-m-d H:i:s'),
        ));
    }

    public function findAllGidFrom($datetime) {
        $sql = "SELECT distinct(gid) FROM *PREFIX*dashboard_history_by_group WHERE date > ? ORDER BY gid";
        return $this->findEntities($sql, array(
            $datetime->format('Y-m-d H:i:s'),
        ));
    }

    public function findAllFrom($gid, $datetime) {
        $sql = "SELECT * FROM *PREFIX*dashboard_history_by_group WHERE date > ? AND gid = ? ORDER BY date";
        return $this->findEntities($sql, array(
            $datetime->format('Y-m-d H:i:s'),
            $gid,
        ));
    }
}
