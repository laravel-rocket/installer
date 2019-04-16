<?php

namespace LaravelRocket\Installer\Tasks;

use LaravelRocket\Installer\Services\FileService;
use LaravelRocket\Installer\Services\ProcessService;
use LaravelRocket\Installer\Services\StringService;
use LaravelRocket\Installer\Services\TemplateService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;

class App extends BaseTask
{
    public static function arguments(&$command)
    {
        $command->addArgument(
            'name',
            InputArgument::OPTIONAL
        )->addOption(
            'dir',
            null,
            InputOption::VALUE_NONE,
            'Directory ( Default: Current Directory )'
        );
    }

    public function preprocess($data)
    {
        return $data;
    }

    public function dialog($data)
    {
        $name = $this->input->getArgument('name');
        if (empty($name)) {
            $name = $this->askQuestion('What is the app name ? ', 'AwesomeApp');
            if (empty($name)) {
                throw new RuntimeException('Need to specify App Name');
            }
        }

        $directory = $this->input->getOption('dir');
        if (empty($directory)) {
            $directory = getcwd();
        }

        $data['appName']      = (string) $name;
        $data['appDirectory'] = (string) $directory;

        $this->verifyApplicationDoesntExist($directory);

        return $data;
    }

    public function update($data)
    {
        $name = $data['appName'];
        $directory = $data['appDirectory'];

        $branch = 'master';

        FileService::download($zipFile = FileService::makeFilename($directory), $branch);
        FileService::extract(
            $name,
            $zipFile,
            $directory,
            $branch
        );
        FileService::cleanUp($zipFile);

        $composer = FileService::findComposer();
        $commands = [
            $composer . ' install --no-scripts',
            $composer . ' run-script post-root-package-install',
            $composer . ' run-script post-install-cmd',
            $composer . ' run-script post-create-project-cmd',
            $composer . ' update',
        ];

        ProcessService::run($commands, $name, $directory, $this->verboseMode, $this->output);

        $this->replaceAppName($name, $directory);

        return $data;
    }

    /**
     * @param string $name
     * @param string $directory
     */
    protected function replaceAppName(string $name, string $directory)
    {
        $appDirectory = realpath($directory . DIRECTORY_SEPARATOR . $name);
        $cookieName   = str_replace(['-'], '_', StringService::camel2Snake($name)) . '_session';

        $appConfigPath = realpath($appDirectory . '/config/app.php');
        TemplateService::replace([
            'NAME' => $name,
        ], $appConfigPath);

        $siteConfigPath = realpath($appDirectory . '/config/site.php');
        TemplateService::replace([
            'NAME' => $name,
        ], $siteConfigPath);

        $appConfigPath = realpath($appDirectory . '/config/session.php');
        TemplateService::replace([
            'SESSION_NAME' => $cookieName,
        ], $appConfigPath);
    }

    /**
     * @param string $directory
     */
    protected function verifyApplicationDoesntExist(string $directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }
}
