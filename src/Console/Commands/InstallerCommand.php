<?php

namespace LaravelRocket\Installer\Console\Commands;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InstallerCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('new')->setDescription('Create a new Laravel Rocket boilerplate.')->addArgument(
            'name',
            InputArgument::OPTIONAL
        )->addOption(
            'dev',
            null,
            InputOption::VALUE_NONE,
            'Installs the latest "development" release'
        )->addOption(
            'database-host',
            null,
            InputOption::VALUE_NONE,
            'Database hostname'
        )->addOption(
            'database-user',
            null,
            InputOption::VALUE_NONE,
            'Database username'
        )->addOption(
            'database-password',
            null,
            InputOption::VALUE_NONE,
            'Database password'
        );
    }

    protected function handle()
    {
        $input  = $this->input;
        $output = $this->output;

        $verboseMode = empty($input->getOption('verbose')) ? false : true;

        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
        }

        $name = $input->getArgument('name');
        if (empty($name)) {
            $name = $this->askQuestion('What is the app name ? ', 'AwesomeApp');
            if (empty($name)) {
                throw new RuntimeException('Need to specify App Name');
            }
        }

        $databaseHostname = $input->getOption('database-host');
        if (empty($databaseHostname)) {
            $databaseHostname = $this->askQuestion('What is the host name of the database of your development environment ( default: localhost ) ? ', 'localhost');
            if (empty($databaseHostname)) {
                throw new RuntimeException('Need to specify Database hostname');
            }
        }

        $databaseUsername = $input->getOption('database-user');
        if (empty($databaseUsername)) {
            $databaseUsername = $this->askQuestion('What is the user name of the database of your development environment  ( default: root ) ?', 'root');
            if (empty($databaseUsername)) {
                throw new RuntimeException('Need to specify Database username');
            }
        }

        $databasePassword = $input->getOption('database-password');
        if (empty($databaseUser)) {
            $databasePassword = $this->askQuestion('What is the password of the database of your development environment ?', '');
        }

        $this->verifyApplicationDoesntExist($this->makeAppDirectory($name));
        $directory = getcwd();

        $this->output('Setting up application "' . $name . '" with Laravel Rocket 🚀 ...', 'info');

        $branch = 'master';

        $this->download($zipFile = $this->makeFilename(), $branch)->extract(
            $name,
            $zipFile,
            $directory,
            $branch
        )->cleanUp($zipFile);

        $composer = $this->findComposer();
        $commands = [
            $composer . ' install --no-scripts',
            $composer . ' run-script post-root-package-install',
            $composer . ' run-script post-install-cmd',
            $composer . ' run-script post-create-project-cmd',
            $composer . ' update',
        ];

        $process = new Process(implode(' && ', $commands), $this->makeAppDirectory($name), null, null, null);

        $process->run(function ($type, $line) use ($output, $verboseMode) {
            if ($verboseMode) {
                $output->write($line);
            }
        });

        $this->replaceAppName($name, $directory);

        $this->updateEnvironment($name, $directory, [
            'DB_HOST'     => $databaseHostname,
            'DB_DATABASE' => $this->camel2Snake($name),
            'DB_USERNAME' => $databaseUsername,
            'DB_PASSWORD' => $databasePassword,
        ]);

        $this->outputNewLine();
        $this->outputNewLine();

        $this->output('Application "' . $name . '"" ready! Build something amazing 🛫 .', 'comment');

        $this->outputNewLine();
        $this->outputNewLine();

        $this->output('Your Next Step Is ... ', 'blue');

        $this->outputNewLine();

        $this->output('   1. Define database schema and update </blue><green>/documents/db.mwb</green><blue> file with MySQL Workbench ( https://dev.mysql.com/downloads/workbench/ )', 'blue');
        $this->output('   2. run </blue><green>php artisan rocket:generate:from-mwb</green><blue> command to generate required files.', 'blue');
        $this->output('   3. run </blue><green>php artisan migrate</green></green><blue> command to create database.', 'blue');
        $this->output('   4. run </blue><green>>php artisan serve</green><blue> command to run server.', 'blue');
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
        return getcwd() . '/laravel_rocket_' . md5(time() . uniqid()) . '.zip';
    }

    /**
     * @param  string $zipFile
     * @param  string $branch
     *
     * @return $this
     */
    protected function download($zipFile, $branch = 'master')
    {
        $response = (new Client)->get('https://github.com/laravel-rocket/base/archive/' . $branch . '.zip');
        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * @param  string $name
     *
     * @return string
     */
    protected function makeAppDirectory($name)
    {
        return getcwd() . '/' . $name;
    }

    /**
     * @param  string $name
     * @param  string $zipFile
     * @param  string $directory
     * @param  string $branch
     *
     * @return $this
     */
    protected function extract($name, $zipFile, $directory, $branch = 'master')
    {
        $tempDirectoryName = uniqid($name, true);
        $extractDirectory  = $directory . '/' . $tempDirectoryName;
        $appDirectory      = $this->makeAppDirectory($name);

        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo($extractDirectory);
        $archive->close();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->renameWin($extractDirectory . '/base-' . $branch, $appDirectory);
        }else{
            rename($extractDirectory . '/base-' . $branch, $appDirectory);
        }
        rmdir($tempDirectoryName);

        return $this;
    }

    /**
     * @param  string $zipFile
     *
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
        if (file_exists(getcwd() . '/composer.phar')) {
            return '"' . PHP_BINARY . '" composer.phar';
        }

        return 'composer';
    }

    /**
     * @param string $name
     * @param string $directory
     */
    protected function replaceAppName($name, $directory)
    {
        $appDirectory = realpath($directory . DIRECTORY_SEPARATOR . $name);
        $cookieName   = str_replace(['-'], '_', $this->camel2Snake($name)) . '_session';

        $appConfigPath = realpath($appDirectory . '/config/app.php');
        $this->replace([
            'NAME' => $name,
        ], $appConfigPath);

        $siteConfigPath = realpath($appDirectory . '/config/site.php');
        $this->replace([
            'NAME' => $name,
        ], $siteConfigPath);

        $appConfigPath = realpath($appDirectory . '/config/session.php');
        $this->replace([
            'SESSION_NAME' => $cookieName,
        ], $appConfigPath);
    }

    /**
     * @param string $name
     * @param string $directory
     * @param array $data
     */
    protected function updateEnvironment(string $name, string $directory, array $data)
    {
        $envPath = realpath($directory . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . '.env');
        print $envPath;
        if (!file_exists($envPath)) {
            $envSamplePath = realpath($directory . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . '.env.example');
            copy($envSamplePath, $envPath);
        }
        $lines   = file($envPath);
        $updated = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/([^=]+)=(.*)/', $line, $matches)) {
                $key = $matches[1];
                if (array_key_exists($key, $data)) {
                    $updated[] = $key . '=' . $data[$key];
                } else {
                    $updated[] = $line;
                }
            } else {
                $updated[] = $line;
            }
        }

        $updatedEnv = implode(PHP_EOL, $updated) . PHP_EOL;
        file_put_contents($envPath, $updatedEnv);
    }


    /**
     * @param  array $data
     * @param  string $filePath
     */
    protected function replace($data, $filePath)
    {
        $original = file_get_contents($filePath);

        foreach ($data as $key => $value) {
            $templateKey = '%%' . strtoupper($key) . '%%';
            $original    = str_replace($templateKey, $value, $original);
        }

        file_put_contents($filePath, $original);
    }

    /**
     * @param  string $input
     *
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

    private function renameWin($oldName,$newName) {
        if (!rename($oldName,$newName)) {
            if (copy ($oldName,$newName)) {
                unlink($oldName);
                return TRUE;
            }
            return FALSE;
        }
        return TRUE;
    }
}
