<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Service;

use \OCA\Dashboard\Db\HistoryMapper;
use \OCA\Dashboard\Db\History;
use \OCA\Dashboard\Db\HistoryByGroupMapper;
use \OCA\Dashboard\Db\HistoryByGroup;

use OCA\Dashboard\Lib\Helper;

class StatsTaskService {
    protected $statService;
    protected $historyMapper;
    protected $historyByGroupMapper;

    public function __construct(\OCA\Dashboard\Service\StatService $statService,HistoryMapper $historyMapper,HistoryByGroupMapper $historyByGroupMapper) {
        $this->statService = $statService;
        $this->historyMapper = $historyMapper;
        $this->historyByGroupMapper = $historyByGroupMapper;
    }

    /**
     * Run cron job and store some basic stats in DB
     */
    public function run() {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $datas = $this->historyMapper->countFrom($now);
        if (count($datas) <= 0) {
            $globalStorageInfo = $this->statService->getGlobalStorageInfo();
            $now = new \DateTime();
            $now = $now->format("Y-m-d H:i:s");

            $history = $this->getStats($globalStorageInfo, $now);
            $this->historyMapper->insert($history);

            // stats by group ?
            if (Helper::isDashboardGroupsEnabled() and !empty($globalStorageInfo['groups'])) {
                // One stat line per group for today
                foreach($globalStorageInfo['groups'] as $groupName => $groupInfo) {
                    $historyByGroup = $this->getStatsByGroup($groupName, $groupInfo, $now);
                    $this->historyByGroupMapper->insert($historyByGroup);
                }
            }
        }
    }

    /**
     * @return \OCA\Dashboard\Db\History
     */
    protected function getStats($globalStorageInfo, $when) {
        $history = new History;

        $history->setDate($when);
        $history->setNbUsers($this->statService->countUsers());
        $history->setDefaultQuota($globalStorageInfo['defaultQuota']);
        $history->setNbFolders($globalStorageInfo['totalFolders']);
        $history->setNbFiles($globalStorageInfo['totalFiles']);
        $history->setNbShares($globalStorageInfo['totalShares']);
        $history->setTotalUsedSpace($globalStorageInfo['totalSize']);
        $history->setSizePerUser($globalStorageInfo['sizePerUser']);
        $history->setFoldersPerUser($globalStorageInfo['foldersPerUser']);
        $history->setFilesPerUser($globalStorageInfo['filesPerUser']);
        $history->setSharesPerUser($globalStorageInfo['sharesPerUser']);
        $history->setSizePerFolder($globalStorageInfo['sizePerFolder']);
        $history->setFilesPerFolder($globalStorageInfo['filesPerFolder']);
        $history->setSizePerFile($globalStorageInfo['sizePerFile']);
        $history->setStdvFilesPerUser($globalStorageInfo['stdvNbFilesPerUser']);
        $history->setStdvFoldersPerUser($globalStorageInfo['stdvNbFoldersPerUser']);
        $history->setStdvSharesPerUser($globalStorageInfo['stdvNbSharesPerUser']);

        return $history;
    }

    /**
     * @param string $groupName Group gid
     * @param array $groupInfo Group stats
     * @param string $when datetime
     * @return \OCA\Dashboard\Db\HistoryByGroup
     */
    protected function getStatsByGroup($groupName, $groupInfo, $when) {
        $historyByGroup = new HistoryByGroup;

        $historyByGroup->setGid($groupName);
        $historyByGroup->setDate($when);
        $historyByGroup->setTotalUsedSpace($groupInfo['filesize']);
        $historyByGroup->setNbUsers($groupInfo['nbUsers']);
        $historyByGroup->setNbFolders($groupInfo['nbFolders']);
        $historyByGroup->setNbFiles($groupInfo['nbFiles']);
        $historyByGroup->setNbShares($groupInfo['nbShares']);
        $historyByGroup->setSizePerUser($groupInfo['sizePerUser']);
        $historyByGroup->setFilesPerUser($groupInfo['filesPerUser']);
        $historyByGroup->setFoldersPerUser($groupInfo['foldersPerUser']);
        $historyByGroup->setSharesPerUser($groupInfo['sharesPerUser']);
        $historyByGroup->setSizePerFolder($groupInfo['sizePerFolder']);
        $historyByGroup->setFilesPerFolder($groupInfo['filesPerFolder']);
        $historyByGroup->setSizePerFile($groupInfo['sizePerFile']);

        return $historyByGroup;
    }
}
