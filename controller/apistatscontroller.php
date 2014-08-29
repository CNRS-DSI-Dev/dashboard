<?php

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\APIController;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IRequest;

class APIStatsController extends APIController {

    public function __construct($appName, IRequest $request, IConfig $settings, $userId, $statService){
        parent::__construct($appName, $request, 'GET');
        $this->settings = $settings;
        $this->userId = $userId;
        $this->statService = $statService;
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
            'globalFreeSpace'   => $this->statService->globalFreeSpace(),
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

}