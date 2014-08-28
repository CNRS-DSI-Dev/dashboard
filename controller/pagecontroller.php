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
        return $this->render('main');
    }

    protected function formatSize($size) {
        return \OCP\Util::humanFileSize($size);
    }

    protected function formatNumber($number) {
        return sprintf("%.2f", $number);
    }


}