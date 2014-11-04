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

    /**
     * Returns groups from given date if there is more than one data (displaying chart needs at least two datas)
     * @param \DateTime $datetime From when you want to get groups
     */
    public function findAllGidFrom($datetime) {
        $sql = "SELECT distinct(gid) FROM *PREFIX*dashboard_history_by_group WHERE date > ? GROUP BY gid HAVING COUNT(gid) > 1 ORDER BY gid";
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
