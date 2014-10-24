<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;
use \OCP\IConfig;

class APIStatsController extends APIController {

    protected $settings;
    protected $userId;
    protected $statService;
    protected $historyService;

    public function __construct($appName, IRequest $request, IConfig $settings, $userId, $statService, $historyService){
        parent::__construct($appName, $request, 'GET');
        $this->settings = $settings;
        $this->userId = $userId;
        $this->statService = $statService;
        $this->historyService = $historyService;
    }

    /**
     * Returns informations from history
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function index() {
        $stats = array(
            'uid'               => $this->userId,
            'appVersion'        => $this->settings->getAppValue($this->appName, 'installed_version'),
            'userLastLogin'     => date('d/m/Y H:i:s', $this->settings->getUserValue($this->userId, 'login', 'lastLogin')),
            'userDataDir'       => $this->statService->getUserDataDir(),
        );

        try {
            $history = $this->historyService->getHistoryStats('all', 1);
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCP\AppFramework\Http::STATUS_NOT_FOUND);
        }

        $stats['history'] = $history;
        foreach($stats['history'] as $key => $value) {
            if (isset($value[0])) {
                $stats['history'][$key] = $value[0];
            }
            else {
                $stats['history'][$key] = '';
            }
        }

        $this->registerResponder('xml', function($stats){
            return new XMLResponse($stats);
        });

        return new JSONResponse($stats);
    }

    /**
     * Returns real time informations
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
     * @NoAdminRequired
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
     * Get stats for a number of days
     * @NoAdminRequired
     * @NoCSRFRequired
     * @CORS
     */
    public function historyStats($dataType='all', $range=30, $wanthumanreadable=1) {
        $history = array();

        try {
            $history = $this->historyService->getHistoryStats($dataType, $range, $wanthumanreadable);
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCP\AppFramework\Http::STATUS_NOT_FOUND);
        }

        return new JSONResponse($history);
    }

}
