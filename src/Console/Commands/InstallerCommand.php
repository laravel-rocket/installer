<?php

namespace LaravelRocket\Installer\Console\Commands;

class InstallerCommand extends BaseCommand
{
    protected $tasks = [
        \LaravelRocket\Installer\Tasks\CheckRequirements::class,
        \LaravelRocket\Installer\Tasks\App::class,
        \LaravelRocket\Installer\Tasks\Database::class,
        \LaravelRocket\Installer\Tasks\UnitTest::class,
    ];

    protected function configure()
    {
        parent::configure();
        $this->setName('new')->setDescription('Create a new Laravel Rocket boilerplate.');
    }

    protected function onBeforeUpdate($data)
    {
        $this->output('Setting up application "' . $data['appName'] . '" with Laravel Rocket 🚀 ...', 'info');
        return $data;
    }

    protected function onAfterUpdate($data)
    {
        $this->outputNewLine();
        $this->outputNewLine();

        $this->output('Application "' . $data['appName'] . '"" ready! Build something amazing 🛫 .', 'comment');

        $this->outputNewLine();
        $this->outputNewLine();

        $this->output('Your Next Step Is ... ', 'blue');

        $this->outputNewLine();

        $this->output('   1. Define database schema and update <fg=green>/documents/db.mwb</> file with MySQL Workbench ( https://dev.mysql.com/downloads/workbench/ )', 'blue');
        $this->output('   2. run <fg=green>php artisan rocket:generate:from-mwb</> command to generate required files.', 'blue');
        $this->output('   3. run <fg=green>php artisan migrate</> command to create database.', 'blue');
        $this->output('   4. run <fg=green>php artisan serve</> command to run server.', 'blue');

        return $data;
    }

    /*
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
    
            $this->output('   1. Define database schema and update <fg=green>/documents/db.mwb</> file with MySQL Workbench ( https://dev.mysql.com/downloads/workbench/ )', 'blue');
            $this->output('   2. run <fg=green>php artisan rocket:generate:from-mwb</> command to generate required files.', 'blue');
            $this->output('   3. run <fg=green>php artisan migrate</> command to create database.', 'blue');
            $this->output('   4. run <fg=green>php artisan serve</> command to run server.', 'blue');
        }
    
     */
}
