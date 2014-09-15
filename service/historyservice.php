<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Service;

class HistoryService {

    protected $historyMapper;

    public function __construct(\OCA\Dashboard\Db\HistoryMapper $historyMapper) {
        $this->historyMapper = $historyMapper;
    }

    /**
     * Returns datas from history
     * @param string $dataType The type of data you want, 'all' (default) if you want all datas.
     * @param integer $range Number of days from today you want to get the datas
     * @throws OCA\Dashboard\Service\HistoryStatsUnknownDatatypeException
     * @throws OCA\Dashboard\Service\HistoryStatsInvalidRangeException
     */
    public function getHistoryStats($dataType='all', $range=30) {
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
                throw new HistoryStatsUnknownDatatypeException();

            }
            $statName = array('date', $dataType);
        }

        if (intval($range) <= 0) {
            throw new HistoryStatsInvalidRangeException();

        }

        $history = array();
        foreach($statName as $name) {
            $history[$name] = '';
        }

        // by 30d (30 last days)
        $datetime = new \DateTime();
        $datetime->sub(new \dateInterval('P' . (int)$range . 'D'));
        $datetime->setTime(23, 59, 59);
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

        return $history;
    }
}