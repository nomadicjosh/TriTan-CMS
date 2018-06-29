<?php

namespace TriTan\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SystemCommand extends Command
{

    /**
     * In this method setup command, description and its parameters
     */
    protected function configure()
    {
        $this->setName('env');
        $this->setDescription('You can print environment info, see table data, or backup system and/or database.');
        $this->addOption('type', '-t', InputOption::VALUE_REQUIRED, 'Type of information to show or run.');
        $this->addOption('backup', 'b', InputOption::VALUE_REQUIRED, 'Type of backup: system or database.');
        $this->addOption('dest', 'd', InputOption::VALUE_REQUIRED, 'Destination of backup.');
    }

    protected function backup($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, $zip::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    /**
     * Here all logic happens
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backup = $input->getOption('backup');
        $dest = $input->getOption('dest');
        $type = $input->getOption('type');

        if ($type === 'db') {
            $documents = glob('private' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR . '*.json');
            if (is_array($documents)) {
                foreach ($documents as $document) {
                    $output->writeln(sprintf(
                                    'Table: <comment>%s</comment>', str_replace(['private' . DIRECTORY_SEPARATOR . 'db' . DIRECTORY_SEPARATOR, '.json'], '', $document)
                    ));
                }
            }
            $output->writeln('Database table list complete.');
            return 0;
        }

        if ($type === 'archive') {
            if ($backup === 'system') {
                $dir = rtrim($dest, DIRECTORY_SEPARATOR);
                $destination = $dir . DIRECTORY_SEPARATOR . \Jenssegers\Date\Date::now()->format('Y-m-d-h:m:s') . '_system-backup.zip';

                $output->writeln(sprintf('Backing up system to %s.', $destination));
                $this->backup(realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR), $destination);
                $output->writeln('System backup complete.');
                return 0;
            } elseif ($backup === 'db') {
                $dir = rtrim($dest, DIRECTORY_SEPARATOR);
                $destination = $dir . DIRECTORY_SEPARATOR . \Jenssegers\Date\Date::now()->format('Y-m-d-h:m:s') . '_ttcms-dump.zip';

                $output->writeln(sprintf('Backing up database to %s.', $destination));
                $this->backup('private' . DS . 'db' . DS, $destination);
                $output->writeln('Database backup complete.');
                return 0;
            }
        }

        $php_bin = defined('PHP_BINARY') ? PHP_BINARY : getenv('TTCMS_CLI_PHP_USED');

        $output->writeln(sprintf(
                        'PHP binary: <comment>%s</comment>', $php_bin
        ));
        $output->writeln(sprintf(
                        'PHP version: <comment>%s</comment>', PHP_VERSION
        ));
        $output->writeln(sprintf(
                        'PHP Modules: <comment>%s</comment>', shell_exec('php -m')
        ));
        $output->writeln(sprintf(
                        'LaciDb Version: <comment>%s</comment>', 'v0.3.0'
        ));
        $output->writeln(sprintf(
                        'TriTan CMS: <comment>%s</comment>', file_get_contents('RELEASE')
        ));

        // return value is important when using CI
        // to fail the build when the command fails
        // 0 = success, other values = fail
        return 0;
    }

}
