<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Controller;

use \OCP\AppFramework\Controller;
use \OCP\IRequest;
use \OCP\IL10N;

class PageController extends Controller {

    protected $trans;

    public function __construct($appName, IRequest $request, IL10N $trans){
        parent::__construct($appName, $request);

        $this->trans = $trans;
    }

    public function getLanguageCode() {
        return $this->trans->getLanguageCode();
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
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
