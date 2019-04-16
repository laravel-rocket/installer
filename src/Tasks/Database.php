<?php

namespace LaravelRocket\Installer\Tasks;

use LaravelRocket\Installer\Services\FileService;
use LaravelRocket\Installer\Services\StringService;
use Symfony\Component\Console\Input\InputOption;
use RuntimeException;

class Database extends BaseTask
{

	static public function arguments(&$command)
	{
		$command->addOption(
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
		)->addOption(
			'database-name',
			null,
			InputOption::VALUE_NONE,
			'Database name'
		);
	}

	public function preprocess($data)
	{
		return $data;
	}

	public function dialog($data)
	{
		$databaseHostname = $this->input->getOption('database-host');
		if(empty($databaseHostname)) {
			$databaseHostname = $this->askQuestion('What is the host name of the database of your development environment ( default: localhost ) ? ', 'localhost');
			if(empty($databaseHostname)) {
				throw new RuntimeException('Need to specify Database hostname');
			}
		}

		$databaseUsername = $this->input->getOption('database-user');
		if(empty($databaseUsername)) {
			$databaseUsername = $this->askQuestion('What is the user name of the database of your development environment  ( default: root ) ? ', 'root');
			if(empty($databaseUsername)) {
				throw new RuntimeException('Need to specify Database username');
			}
		}

		$databasePassword = $this->input->getOption('database-password');
		if(empty($databaseUser)) {
			$databasePassword = $this->askQuestion('What is the password of the database of your development environment ( default: no password ) ? ', '');
		}

		$databaseName = $this->input->getOption('database-name');
		if(empty($databaseName)) {
			$defaultDatabaseName = StringService::camel2Snake($data['appName']);
			$databaseName = $this->askQuestion('What is the name of the database of your development environment ( default: '.$defaultDatabaseName.' ) ? ', $defaultDatabaseName);
		}

		$data['databaseHostname'] = $databaseHostname;
		$data['databaseUsername'] = $databaseUsername;
		$data['databasePassword'] = $databasePassword;
		$data['databaseName']     = $databaseName;

		return $data;
	}

	public function update($data)
	{
		FileService::updateEnvironment($data['appName'], $data['appDirectory'], [
			'DB_HOST'     => $data['databaseHostname'],
			'DB_DATABASE' => $data['databaseName'],
			'DB_USERNAME' => $data['databaseUsername'],
			'DB_PASSWORD' => $data['databasePassword'],
		]);

		return $data;
	}
}
