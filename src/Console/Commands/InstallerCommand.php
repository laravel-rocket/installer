<?php

namespace LaravelRocket\Installer\Console\Commands;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Colors\Color;

class InstallerCommand extends Command
{

    protected function configure()
    {
        $this->setName('new')->setDescription('Create a new Laravel Rocket boilerplate.')->addArgument('name',
            InputArgument::OPTIONAL)->addOption('dev', null, InputOption::VALUE_NONE,
            'Installs the latest "development" release');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $color = new Color();

        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        $name = $input->getArgument('name');
        if (empty($name)) {
            throw new RuntimeException('You need to specify app name');
        }

        $this->verifyApplicationDoesntExist($this->makeAppDirectory($name));
        $directory = getcwd();

        $output->writeln('<info>Setting up application "'.$name.'" with Laravel Rocket 🚀 ...</info>');

        $output->writeln('');
        $helper = $this->getHelper('question');
        $questionText = $color('Do you want to use AdminLTE for Admin Dashboard?')->green()->bold().' ';
        $question = new ConfirmationQuestion($questionText, false);

        $branch = $helper->ask($input, $output, $question) ? 'adminlte' : 'master';

        $this->download($zipFile = $this->makeFilename(), $branch)->extract($name, $zipFile, $directory,
            $branch)->cleanUp($zipFile);

        $composer = $this->findComposer();
        $commands = [
            $composer.' install --no-scripts',
            $composer.' run-script post-root-package-install',
            $composer.' run-script post-install-cmd',
            $composer.' run-script post-create-project-cmd',
        ];

        $process = new Process(implode(' && ', $commands), $this->makeAppDirectory($name), null, null, null);

        $process->run(function($type, $line) use ($output) {
            $output->write($line);
        });

        $this->replaceAppName($name, $directory);


        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }

    /**
     * @param string $directory
     */
    protected function verifyApplicationDoesntExist($directory)
    {
        if ((is_dir($directory) || is_file($directory)) && $directory != getcwd()) {
            throw new RuntimeException('Application already exists!');
        }
    }

    /**
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd().'/laravel_rocket_'.md5(time().uniqid()).'.zip';
    }

    /**
     * @param  string $zipFile
     * @param  string $branch
     * @return $this
     */
    protected function download($zipFile, $branch = 'master')
    {
        $response = (new Client)->get('https://github.com/laravel-rocket/base/archive/'.$branch.'.zip');
        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function makeAppDirectory($name)
    {
        return getcwd().'/'.$name;
    }

    /**
     * @param  string $name
     * @param  string $zipFile
     * @param  string $directory
     * @param  string $branch
     * @return $this
     */
    protected function extract($name, $zipFile, $directory, $branch = 'master')
    {
        $tempDirectoryName = uniqid($name, true);
        $extractDirectory = $directory.'/'.$tempDirectoryName;
        $appDirectory = $this->makeAppDirectory($name);

        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo($extractDirectory);
        $archive->close();

        rename($extractDirectory.'/base-'.$branch, $appDirectory);
        rmdir($tempDirectoryName);

        return $this;
    }

    /**
     * @param  string $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);
        @unlink($zipFile);

        return $this;
    }

    /**
     * @return string
     */
    protected function findComposer()
    {
        if (file_exists(getcwd().'/composer.phar')) {
            return '"'.PHP_BINARY.'" composer.phar';
        }

        return 'composer';
    }

    /**
     * @param string $name
     * @param string $directory
     */
    protected function replaceAppName($name, $directory)
    {
        $appDirectory = realpath($directory.'/'.$name);
        $cookieName = str_replace(['-'], '_', $this->camel2Snake($name)).'_session';

        $appConfigPath = realpath($appDirectory.'/config/app.php');
        $this->replace([
            'NAME' => $name,
        ], $appConfigPath);

        $siteConfigPath = realpath($appDirectory.'/config/site.php');
        $this->replace([
            'NAME' => $name,
        ], $siteConfigPath);

        $appConfigPath = realpath($appDirectory.'/config/session.php');
        $this->replace([
            'SESSION_NAME' => $cookieName,
        ], $appConfigPath);

    }

    /**
     * @param  array $data
     * @param  string $filePath
     * @return string
     */
    protected function replace($data, $filePath)
    {
        $original = file_get_contents($filePath);

        foreach ($data as $key => $value) {
            $templateKey = '%%'.strtoupper($key).'%%';
            $original = str_replace($templateKey, $value, $original);
        }

        file_put_contents($filePath, $original);
    }

    /**
     * @param  string $input
     * @return string
     */
    private function camel2Snake($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}