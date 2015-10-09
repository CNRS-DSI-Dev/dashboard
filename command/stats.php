<?php

/**
 * ownCloud - Dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Dashboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use OCA\Dashboard\Service\StatsTaskService;

class Stats extends Command {

    protected $statsTaskService;

    public function __construct(StatsTaskService $statsTaskService)
    {
        $this->statsTaskService = $statsTaskService;
        parent::__construct();
    }

    protected function configure($kikoo)
    {
        $prefix = \OCP\Config::getSystemValue('dbtableprefix', 'oc_');

        $this
            ->setName('dashboard:stats')
            ->setDescription('Get realtime stats and insert them into '
                . $prefix . 'dashboard_history and '
                . $prefix . 'dashboard_history_by_group (if needed) tables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Beginning');

        $this->statsTaskService->run();

        $output->writeln('Done');
    }


}
