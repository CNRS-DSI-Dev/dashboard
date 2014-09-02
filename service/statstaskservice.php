<?php
namespace OCA\Dashboard\Service;

use \OCA\Dashboard\Db\HistoryMapper;
use \OCA\Dashboard\Db\History;


class StatsTaskService {
    protected $statService;
    protected $historyMapper;

    public function __construct(\OCA\Dashboard\Service\StatService $statService, $historyMapper) {
        $this->statService = $statService;
        $this->historyMapper = $historyMapper;
    }

    /**
     * Run cron job and store some basic stats in DB
     */
    public function run() {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $datas = $this->historyMapper->countFrom($now->format('Y-m-d H:i:s'));
        if (count($datas) <= 0) {
            $history = $this->getStats();
            $this->historyMapper->insert($history);
        }
    }

    /**
     * @return \OCA\Dashboard\Db\History
     */
    protected function getStats() {
        $globalStorageInfo = $this->statService->getGlobalStorageInfo();

        $history = new History;

        $history->setDate(date("Y-m-d H:i:s"));
        $history->setNbUsers($this->statService->countUsers());
        $history->setDefaultQuota($globalStorageInfo['defaultQuota']);
        $history->setNbFolders($globalStorageInfo['totalFolders']);
        $history->setNbFiles($globalStorageInfo['totalFiles']);
        $history->setNbShares($globalStorageInfo['totalShares']);
        $history->setTotalUsedSpace($globalStorageInfo['totalSize']);

        return $history;
    }
}
