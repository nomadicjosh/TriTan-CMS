<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CheckUpdateCommand extends Command
{

    /**
     * In this method setup command, description and its parameters
     */
    protected function configure()
    {
        $this->setName('check-update');
        $this->setDescription('Checks to see if installed release is the up to date release.');
    }

    /**
     * Here all logic happens
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $update = new \ParkerJ\AutoUpdate('static' . DIRECTORY_SEPARATOR . 'tmp', 'static' . DIRECTORY_SEPARATOR . 'tmp', 1800);
        $update->setCurrentVersion(trim(file_get_contents('RELEASE')));
        $update->setUpdateUrl('http://tritan-cms.s3.amazonaws.com/api/1.1/update-check');

        // Optional:
        $update->addLogHandler(new \Monolog\Handler\StreamHandler('static' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARTOR . 'core-update.' . \Jenssegers\Date\Date::now()->format('m-d-Y') . '.txt'));
        $update->setCache(new \Desarrolla2\Cache\Adapter\File('static' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache'), 3600);

        if ($update->checkUpdate() !== false) {
            if ($update->newVersionAvailable()) {
                $output->writeln(sprintf(
                                'Release %s is available', $update->getLatestVersion()
                ));
            } else {
                $output->writeln(sprintf(
                                'TriTan CMS %s is at the latest release.', file_get_contents(RELEASE)
                ));
            }
        }

        // return value is important when using CI
        // to fail the build when the command fails
        // 0 = success, other values = fail
        return 0;
    }

}
