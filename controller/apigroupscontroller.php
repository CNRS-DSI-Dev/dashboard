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
     * @NoAdminRequired
     * @NoCSRFRequired
     * @return boolean
     */
    public function isGroupsEnabled()
    {
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
        \OCP\JSON::checkAdminUser();

        $groups = array();

        try {
            $groups = $this->groupsService->groups($search);
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
        }

        return new JSONResponse($groups);
    }

    /**
     * Returns list of stat's enabled groups
     * @NoAdminRequired
     * @NoCSRFRequired
     * @param int $range Number of days from today you want to get the groups
     * @return array
     */
    public function statsEnabledGroups($range=30)
    {
        // TODO: THROW EXCEPTION IF RANGE UNAVAILABLE (REF HISTORYSTATS_INVALID_RANGE_EXCEPTION)

        $groups = array();

        try {
            $datas = $this->groupsService->statsEnabledGroups($range);

            $groups = array_map(function($element){return array('id' => $element->getGid());}, $datas);
        } catch (Exception $e) {
            $response = new JSONResponse();
            return $response->setStatus(\OCA\AppFramework\Http::STATUS_NOT_FOUND);
        }

        // return new JSONResponse(array(
        //     'groups' => $groups,
        // ));
        return array(
            'groups' => $groups,
        );
    }

}
