<?php

namespace LaravelRocket\Installer\Console\Commands;

class InstallerCommand extends BaseCommand
{
    protected $tasks = [
        \LaravelRocket\Installer\Tasks\CheckRequirements::class,
        \LaravelRocket\Installer\Tasks\App::class,
        \LaravelRocket\Installer\Tasks\Database::class,
        \LaravelRocket\Installer\Tasks\UnitTest::class,
        \LaravelRocket\Installer\Tasks\Frontend::class,
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
}
