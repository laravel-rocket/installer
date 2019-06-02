<?php

namespace LaravelRocket\Installer\Console\Commands;

use LaravelRocket\Installer\Tasks\CheckRequirements;
use LaravelRocket\Installer\Tasks\App;
use LaravelRocket\Installer\Tasks\Database;
use LaravelRocket\Installer\Tasks\UnitTest;
use LaravelRocket\Installer\Tasks\Frontend;

class InstallerCommand extends BaseCommand
{
    protected $tasks = [
        CheckRequirements::class,
        App::class,
        Database::class,
        UnitTest::class,
        Frontend::class,
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

        $this->output('Application "' . $data['appName'] . '" is ready! Let\'s create something amazing 🛫 .', 'comment');

        $this->outputNewLine();
        $this->outputNewLine();

        $this->output('Next steps are ... ', 'blue');

        $this->outputNewLine();

        $this->output('   1. Define database schema and update <fg=green>/documents/db.mwb</> file with MySQL Workbench ( https://dev.mysql.com/downloads/workbench/ )', 'blue');
        $this->output('   2. run <fg=green>php artisan rocket:generate:from-mwb</> command to generate required files.', 'blue');
        $this->output('   3. run <fg=green>php artisan migrate</> command to create database.', 'blue');
        $this->output('   4. run <fg=green>php artisan serve</> command to run server.', 'blue');

        return $data;
    }
}
