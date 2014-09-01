<?php

namespace OCA\Dashboard\Db;

use \OCP\IDb;
use \OCP\AppFramework\Db\Mapper;

class HistoryMapper extends Mapper {
    public function __construct(IDb $db) {
        parent::__construct($db, 'dashboard_history');
    }

    public function findAll($limit=null, $offset=null) {
        $sql = "SELECT * FROM *PREFIX*dashboard_history";
        return $this->findEntities($sql, $limit, $offset);
    }

    public function findAllFrom($timestamp) {
        $sql = "SELECT * FROM *PREFIX*dashboard_history WHERE date > ? ORDER BY date";
        return $this->findEntities($sql, array(
            $timestamp,
        ));
    }
}