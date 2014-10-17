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

use \OCA\Dashboard\lib\Helper;

class APIGroupsController extends APIController
{

    protected $settings;
    protected $userId;
    protected $groupsService;

    public function __construct($appName, IRequest $request, IConfig $settings, $userId, $groupsService)
    {
        parent::__construct($appName, $request, 'GET');
        $this->settings = $settings;
        $this->userId = $userId;
        $this->groupsService = $groupsService;
    }

    /**
     * Verify if group stats are enabled (see general settings screen, "Dashboard" section)
     * @return boolean
     */
    public function isGroupsEnabled()
    {
        \OC_JSON::checkAdminUser();

        $enabled = 'no';

        try {
            $enabled = $this->groupsService->isGroupsEnabled();
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
        }

        return new JSONResponse(array(
            'enabled' => $enabled,
        ));
    }

    /**
     * Return list of groups
     * @CORS
     * @return array List of groups
     */
    public function groups($search='')
    {
        \OC_JSON::checkAdminUser();

        $groups = array();

        try {
            $groups = $this->groupsService->groups($search);
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
        }

        return new JSONResponse($groups);
    }

}
