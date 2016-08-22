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
use OCA\Dashboard\Service\LoggerService;

class Stats extends Command {

    protected $statsTaskService;
    protected $loggerService;

    public function __construct(StatsTaskService $statsTaskService, LoggerService $loggerService)
    {
        $this->statsTaskService = $statsTaskService;
        $this->loggerService = $loggerService;
        parent::__construct();
    }

    protected function configure()
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
        $this->loggerService->setOutput($output);

        $output->writeln('Beginning - ' . date('d/m/Y'));

        $this->statsTaskService->run();

        $output->writeln('Done - ' . date('d/m/Y'));
    }


}
