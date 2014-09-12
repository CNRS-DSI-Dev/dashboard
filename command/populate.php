<?php

namespace OCA\Dashboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use OC\DB\Connection;

// Number of lines inserted
define("NB", 60);

class Populate extends Command {

    protected function configure() {
        $prefix = \OC_Config::getValue('dbtableprefix', 'oc_');

        $this
            ->setName('dashboard:populate')
            ->setDescription('Populate ' . $prefix . 'dashboard_history table with random test datas');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Test OK');

        $date = new \DateTime();
        $date->sub(new \DateInterval('P' . NB . 'D'));

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

        for($i=0 ; $i < NB; $i++) {
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
        }
    }

    protected function addValue($stat, $value) {
        $stat += $value;

        if ($stat < 0) {
            $stat = -$stat;
        }

        return $stat;
    }

    protected function addHistory($date, $stats) {
        $DB = \OC_DB::getConnection();

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

        $statement = $DB->executeQuery($sql, array(
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
}