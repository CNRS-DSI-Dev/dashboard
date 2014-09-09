<?php

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;

class APIStatsController extends APIController {

    protected $settings;
    protected $userId;
    protected $statService;
    protected $historyMapper;

    public function __construct($appName, IRequest $request, IConfig $settings, $userId, $statService, \OCA\Dashboard\Db\HistoryMapper $historyMapper){
        parent::__construct($appName, $request, 'GET');
        $this->settings = $settings;
        $this->userId = $userId;
        $this->statService = $statService;
        $this->historyMapper = $historyMapper;
    }

    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function stats() {
        $stats = array(
            'uid'               => $this->userId,
            'appVersion'        => $this->settings->getAppValue($this->appName, 'installed_version'),
            'userLastLogin'     => date('d/m/Y H:i:s', $this->settings->getUserValue($this->userId, 'login', 'lastLogin')),
            'nbUsers'           => $this->statService->countUsers(),
            'userDataDir'       => $this->statService->getUserDataDir(),
            'globalStorageInfo' => $this->statService->getGlobalStorageInfo(),
        );

        $this->registerResponder('xml', function($stats){
            return new XMLResponse($stats);
        });

        return new JSONResponse($stats);
    }

    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function spaceUse() {
        $percent = 0;

        $globalFreeSpace = $this->statService->globalFreeSpace();
        $globalStorageInfo = $this->statService->getGlobalStorageInfo();

        $totalSpace = $globalStorageInfo['totalSize'] + $globalFreeSpace;

        $percent = sprintf("%.2f", $globalStorageInfo['totalSize'] * 100 / $totalSpace);

        return new JSONResponse($percent);
    }

    /**
     * @NoCSRFRequired
     * @CORS
     */
    public function historyStats($dataType='all', $range=30) {
        $statName = array(
            'date',
            'defaultQuota',
            'totalUsedSpace',
            'nbUsers',
            'nbFolders',
            'nbFiles',
            'nbShares',
            'sizePerUser',
            'foldersPerUser',
            'filesPerUser',
            'sharesPerUser',
            'sizePerFolder',
            'filesPerFolder',
            'sizePerFile',
            'stdvFilesPerUser',
            'stdvFoldersPerUser',
            'stdvSharesPerUser',
        );

        if ($dataType !== 'all') {
            if (!in_array($dataType, $statName)) {
                $response = new JSONResponse();
                return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
            }
            $statName = array('date', $dataType);
        }

        if (intval($range) <= 0) {
            $response = new JSONResponse();
            return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
        }

        $history = array();
        $history = array();
        foreach($statName as $name) {
            $history[$name] = '';
        }

        // by 30d (30 last days)
        $datetime = new \DateTime();
        $datetime->sub(new \dateInterval('P' . (int)$range . 'D'));
        $datas = $this->historyMapper->findAllFrom($datetime, $dataType);

        $arrayDatas = array();
        foreach($statName as $name) {
            $arrayDatas[$name] = array();
        }
        foreach($datas as $data) {
            foreach($statName as $name) {
                // date need special processing as we only retain day number
                if ($name == "date") {
                    list($date, $time) = explode(' ', $data->getDate());
                    list($year, $month, $day) = explode('-', $date);
                    array_push($arrayDatas['date'], $day);
                }
                else {
                    $func = 'get' . ucfirst($name);
                    array_push($arrayDatas[$name], (float)$data->$func());
                }
            }
        }

        foreach($statName as $name) {
            $history[$name] = $arrayDatas[$name];
        }

        return new JSONResponse($history);
    }

}