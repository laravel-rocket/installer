<?php

namespace LaravelRocket\Installer\Services;

use Symfony\Component\Process\Process;

class ProcessService
{
    /**
     * @param array|string $commands
     * @param string $name
     * @param string $directory
     * @param bool $verboseMode
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public static function run($commands, string $name, string $directory, bool $verboseMode, $output)
    {
        if (!is_array($commands)) {
            $commands = [$commands];
        }

        $process = new Process([implode(' && ', $commands)], FileService::makeDirectoryPath($name, $directory), null, null, null);

        $process->run(function ($type, $line) use ($output, $verboseMode) {
            if ($verboseMode) {
                $output->write($line);
            }
        });
    }

    /**
     * @param $commandName
     *
     * @return bool
     */
    public static function checkCommandExists($commandName)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($commandName)));
        return !empty($return);
    }
}
