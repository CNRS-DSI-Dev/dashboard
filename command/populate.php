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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use OC\DB\Connection;

// Number of lines inserted
define("DEFAULT_NB", 60);

class Populate extends Command {

    protected function configure() {
        $prefix = \OCP\Config::getSystemValue('dbtableprefix', 'oc_');

        $this
            ->setName('dashboard:populate')
            ->setDescription('Populate ' . $prefix . 'dashboard_history and ' . $prefix . 'dashboard_history_by_group (if needed) tables with random test datas')
            ->addArgument('nb', InputArgument::OPTIONAL, 'Number of days you want stats for.', DEFAULT_NB)
            ->addOption('truncate', 't', InputOption::VALUE_NONE, 'Delete all history datas before generating new ones.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Beginning');

        $nb = (int)$input->getArgument('nb');

        if ($input->getOption('truncate')) {
            $this->truncate();
        }

        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . $nb . 'D'));

        // arbitrary starting datas
        $stats['defaultQuota'] = 1073741824; // 1GB
        $stats['totalUsedSpace'] = 221877265;
        $stats['nbUsers'] = 4;
        $stats['nbFolders'] = 50;
        $stats['nbFiles'] = 47;
        $stats['nbShares'] = 5;
        $stats['stdvNbFilesPerUser'] = $stats['nbFiles'];
        $stats['stdvNbFoldersPerUser'] = $stats['nbFolders'];
        $stats['stdvNbSharesPerUser'] = $stats['nbShares'];

        $groupsEnabled = true;
        $groupsEnabledKey = \OCP\Config::getAppValue('dashboard', 'dashboard_groups_enabled', 'yes');
        if ($groupsEnabledKey !== 'yes') {
            $groupsEnabled = false;
        }
        $output->writeln("groupsEnabledKey : " . var_dump($groupsEnabledKey));

        for($i=0 ; $i < $nb; $i++) {
            $date->add(new \DateInterval('P1D'));
            $output->writeln('<info>' . 'Adding stats for date ' . $date->format('Y-m-d H:i:s') . '</info>');

            $way = rand(-1,1);

            $stats['totalUsedSpace'] = $this->addValue($stats['totalUsedSpace'], $way * rand(50000, 500000));
            $stats['nbUsers'] = $this->addValue($stats['nbUsers'], $way * rand(5, 20));
            $stats['nbFolders'] = $this->addValue($stats['nbFolders'], $way * rand(5, 20));
            $stats['nbFiles'] = $this->addValue($stats['nbFiles'], $way * rand(10, 80));
            $stats['nbShares'] = $this->addValue($stats['nbShares'], $way * rand(1, 25));

            $stats['filesPerUser'] = $stats['nbFiles'] / $stats['nbUsers'];
            $stats['filesPerFolder'] = $stats['nbFiles'] / $stats['nbFolders'];
            $stats['foldersPerUser'] = $stats['nbFolders'] / $stats['nbUsers'];
            $stats['sharesPerUser'] = $stats['nbShares'] / $stats['nbUsers'];
            $stats['sizePerUser'] = $stats['totalUsedSpace'] / $stats['nbUsers'];
            $stats['sizePerFile'] = $stats['totalUsedSpace'] / $stats['nbFiles'];
            $stats['sizePerFolder'] = $stats['totalUsedSpace'] / $stats['nbFolders'];

            $stats['stdvNbFilesPerUser'] = rand(2, 5);
            $stats['stdvNbFoldersPerUser'] = rand(2, 5);
            $stats['stdvNbSharesPerUser'] = rand(1, 3);

            $this->addHistory($date, $stats);

            if ($groupsEnabled) {
                $nbGroups = round ($stats['nbUsers'] / 2) ;

                for ($gkey = 1 ; $gkey <= $nbGroups ; $gkey++) {
                    $groupName = 'group_' . $gkey;

                    $groupStats['nbUsers'] = round($stats['nbUsers'] / 3);
                    $groupStats['nbFiles'] = round($stats['nbFiles'] / 3);
                    $groupStats['nbFolders'] = round($stats['nbFolders'] / 3);
                    $groupStats['nbShares'] = round($stats['nbShares'] / 3);
                    $groupStats['filesize'] = round($stats['totalUsedSpace'] / 3);

                    $groupStats['filesPerUser'] = $groupStats['nbFiles'] / $groupStats['nbUsers'];
                    $groupStats['filesPerFolder'] = $groupStats['nbFiles'] / $groupStats['nbFolders'];
                    $groupStats['foldersPerUser'] = $groupStats['nbFolders'] / $groupStats['nbUsers'];
                    $groupStats['sharesPerUser'] = $groupStats['nbShares'] / $groupStats['nbUsers'];
                    $groupStats['sizePerUser'] = $groupStats['filesize'] / $groupStats['nbUsers'];
                    $groupStats['sizePerFile'] = $groupStats['filesize'] / $groupStats['nbFiles'];
                    $groupStats['sizePerFolder'] = $groupStats['filesize'] / $groupStats['nbFolders'];
                }

                $this->addHistoryByGroup($date, $groupName, $groupStats);
            }
        }

        $output->writeln('Done');
    }

    /**
     * Avoid negative values in stats
     * @param int|float $stat Original value
     * @param int|float $value Increment
     * @return int|float Positive value
     */
    protected function addValue($stat, $value) {
        $stat += $value;

        if ($stat < 0) {
            $stat = -$stat;
        }

        return $stat;
    }

    /**
     * DB Insert for global stats
     * @param \DateTime $date Insert date
     * @param array $stats Global stats
     */
    protected function addHistory($date, $stats) {
        $sql = "INSERT INTO *PREFIX*dashboard_history
            SET date = :date,
                total_used_space = :totalUsedSpace,
                default_quota = :defaultQuota,
                nb_users = :nbUsers,
                nb_folders = :nbFolders,
                nb_files = :nbFiles,
                nb_shares = :nbShares,
                size_per_user = :sizePerUser,
                folders_per_user = :foldersPerUser,
                files_per_user = :filesPerUser,
                shares_per_user = :sharesPerUser,
                size_per_folder = :sizePerFolder,
                files_per_folder = :filesPerFolder,
                size_per_file = :sizePerFile,
                stdv_files_per_user = :stdvNbFilesPerUser,
                stdv_folders_per_user = :stdvNbFoldersPerUser,
                stdv_shares_per_user = :stdvNbSharesPerUser";

        $stmt = \OCP\DB::prepare($sql);
        $stmt->execute(array(
            ':date' => $date->format('Y-m-d H:i:s'),
            ':totalUsedSpace' => $stats['totalUsedSpace'],
            ':defaultQuota' => $stats['defaultQuota'],
            ':nbUsers' => $stats['nbUsers'],
            ':nbFolders' => $stats['nbFolders'],
            ':nbFiles' => $stats['nbFiles'],
            ':nbShares' => $stats['nbShares'],
            ':sizePerUser' => $stats['sizePerUser'],
            ':foldersPerUser' =>$stats['foldersPerUser'],
            ':filesPerUser' => $stats['filesPerUser'],
            ':sharesPerUser' => $stats['sharesPerUser'],
            ':sizePerFolder' => $stats['sizePerFolder'],
            ':filesPerFolder' => $stats['filesPerFolder'],
            ':sizePerFile' => $stats['sizePerFile'],
            'stdvNbFilesPerUser' => $stats['stdvNbFilesPerUser'],
            'stdvNbFoldersPerUser' => $stats['stdvNbFoldersPerUser'],
            'stdvNbSharesPerUser' => $stats['stdvNbSharesPerUser'],
        ));
    }

    /**
     * DB Insert for group stats
     * @param \DateTime $date Insert date
     * @param string $groupName Group id
     * @param array $groupStats Group's stats
     */
    protected function addHistoryByGroup($date, $groupName, $groupStats) {
        $sql = "INSERT INTO *PREFIX*dashboard_history_by_group
            SET date = :date,
                gid = :groupId,
                total_used_space = :totalUsedSpace,
                nb_users = :nbUsers,
                nb_folders = :nbFolders,
                nb_files = :nbFiles,
                nb_shares = :nbShares,
                size_per_user = :sizePerUser,
                folders_per_user = :foldersPerUser,
                files_per_user = :filesPerUser,
                shares_per_user = :sharesPerUser,
                size_per_folder = :sizePerFolder,
                files_per_folder = :filesPerFolder,
                size_per_file = :sizePerFile";


        $stmt = \OCP\DB::prepare($sql);
        $stmt->execute(array(
            ':date' => $date->format('Y-m-d H:i:s'),
            ':groupId' => $groupName,
            ':totalUsedSpace' => $groupStats['filesize'],
            ':nbUsers' => $groupStats['nbUsers'],
            ':nbFolders' => $groupStats['nbFolders'],
            ':nbFiles' => $groupStats['nbFiles'],
            ':nbShares' => $groupStats['nbShares'],
            ':sizePerUser' => $groupStats['sizePerUser'],
            ':foldersPerUser' =>$groupStats['foldersPerUser'],
            ':filesPerUser' => $groupStats['filesPerUser'],
            ':sharesPerUser' => $groupStats['sharesPerUser'],
            ':sizePerFolder' => $groupStats['sizePerFolder'],
            ':filesPerFolder' => $groupStats['filesPerFolder'],
            ':sizePerFile' => $groupStats['sizePerFile'],
        ));
    }

    protected function truncate() {
        $sql = "TRUNCATE *PREFIX*dashboard_history";
        $stmt = \OCP\DB::prepare($sql);
        $stmt->execute();

        $sql = "TRUNCATE *PREFIX*dashboard_history_by_group";
        $stmt = \OCP\DB::prepare($sql);
        $stmt->execute();
    }
}
