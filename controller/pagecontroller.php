<?php

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\Controller;
use \OCP\IRequest;

class PageController extends Controller {

    public function __construct($appName, IRequest $request, IConfig $settings, $userId, $statService){
        parent::__construct($appName, $request);
        $this->settings = $settings;
        $this->userId = $userId;
        $this->statService = $statService;
    }


    /**
     * @NoCSRFRequired
     */
    public function index() {

        $stats = array(
            'uid'               => $this->userId,
            'appVersion'        => $this->settings->getAppValue($this->appName, 'installed_version'),
            'userLastLogin'     => date('d/m/Y H:i:s', $this->settings->getUserValue($this->userId, 'login', 'lastLogin')),
            'nbUsers'           => $this->statService->countUsers(),
            'globalFreeSpace'   => \OCP\Util::humanFileSize($this->statService->globalFreeSpace()),
            'userDataDir'       => $this->statService->getUserDataDir(),
            'globalStorageInfo' => $this->statService->getGlobalStorageInfo(),
        );

        return $this->render('main', $stats);
    }


}