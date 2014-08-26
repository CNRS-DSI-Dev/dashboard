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
        return new JSONResponse(array(
            'uid'           => $this->userId,
            'appVersion'    => $this->settings->getAppValue($this->appName, 'installed_version'),
            'userLastLogin' => date('d/m/Y H:i:s', $this->settings->getUserValue($this->userId, 'login', 'lastLogin')),
            'nbUsers'       => $this->statService->countUsers(),
            'globalFreeSpace'   => \OCP\Util::humanFileSize($this->statService->globalFreeSpace()),
            'userDataDir'   => $this->statService->getUserDataDir(),
            'globalStorageInfo' => $this->statService->getGlobalStorageInfo(),
        ));
    }


}