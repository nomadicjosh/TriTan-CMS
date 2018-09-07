<?php
namespace TriTan\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateCommand extends Command
{

    protected function getCurrentRelease()
    {
        $update = new \ParkerJ\AutoUpdate('static' . DIRECTORY_SEPARATOR . 'tmp', 'static' . DIRECTORY_SEPARATOR . 'tmp', 1800);
        $update->setCurrentVersion(trim(file_get_contents('RELEASE')));
        $update->setUpdateUrl('http://tritan-cms.s3.amazonaws.com/api/1.1/update-check');
        $update->addLogHandler(new \Monolog\Handler\StreamHandler('static' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'core-update.' . \Jenssegers\Date\Date::now()->format('m-d-Y') . '.txt'));
        $update->setCache(new \Desarrolla2\Cache\Adapter\File('static' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cache'), 3600);
        if ($update->checkUpdate() !== false) {
            if ($update->newVersionAvailable()) {
                return $update->getLatestVersion();
            }
        }
    }

    protected function checkExternalFile($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
        curl_exec($ch);
        $retCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $retCode;
    }

    protected function getDownload($release, $url)
    {
        $fh = fopen($release, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
        curl_exec($ch);
        curl_close($ch);
        fclose($fh);
    }

    /**
     * In this method setup command, description and its parameters
     */
    protected function configure()
    {
        $this->setName('update');
        $this->setDescription('Update the current system with the latest release.');
        $this->addArgument('release', InputArgument::OPTIONAL, 'The release to install/check for if other than recent release.');
    }

    /**
     * Here all logic happens
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $release = $input->getArgument('release');

        if ($release === null) {
            $release_value = $this->getCurrentRelease();
        } else {
            $release_value = $release;
        }

        $zip  = new \ZipArchive();
        $file = 'http://tritan-cms.s3.amazonaws.com/api/1.1/release/' . $release_value . '.zip';

        if (version_compare(trim(file_get_contents('RELEASE')), $release_value, '<')) {
            if ($this->checkExternalFile($file) == 200) {
                //Download file to the server
                opt_notify(new \cli\progress\Bar('Downloading ', 1000000));
                $this->getDownload($release_value . '.zip', $file);
                //Unzip the file to update
                opt_notify(new \cli\progress\Bar('Unzipping ', 1000000));
                $x = $zip->open($release_value . '.zip');
                if ($x === true) {
                    //Extract file in root.
                    $zip->extractTo(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR));
                    $zip->close();
                    //Remove download after completion.
                    unlink($release_value . '.zip');
                }
                // Check for composer updates
                $output->writeln('Checking for vendor updates.');
                $output->writeln(shell_exec('composer update'));
                // Updates complete
                $output->writeln('Core upgrade complete.');
            } else {
                $output->writeln('Update server cannot be reached. Please try again later.');
            }
        } else {
            $output->writeln('No update needed.');
        }

        // return value is important when using CI
        // to fail the build when the command fails
        // 0 = success, other values = fail
        return 0;
    }
}
