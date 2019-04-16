<?php

namespace LaravelRocket\Installer\Services;

use GuzzleHttp\Client;

class FileService
{
	/**
	 * @param  string $zipFile
	 * @param  string $branch
	 */
	static public function download($zipFile, $branch = 'master')
	{
		$response = (new Client)->get('https://github.com/laravel-rocket/base/archive/' . $branch . '.zip');
		file_put_contents($zipFile, $response->getBody());
	}

	/**
	 * @param $name
	 * @param $directory
	 *
	 * @return string
	 */
	static public function makeDirectoryPath($name, $directory)
	{
		return $directory . '/' . $name;
	}


	/**
	 * @param string $directory
	 *
	 * @return string
	 */
	static public function makeFilename($directory)
	{
		return $directory . DIRECTORY_SEPARATOR . 'laravel_rocket_' . md5(time() . uniqid()) . '.zip';
	}

	/**
	 * @param  string $name
	 * @param  string $zipFile
	 * @param  string $directory
	 * @param  string $branch
	 */
	static public function extract($name, $zipFile, $directory, $branch = 'master')
	{
		$tempDirectoryName = uniqid($name, true);
		$extractDirectory  = $directory . DIRECTORY_SEPARATOR . $tempDirectoryName;
		$appDirectory      = static::makeDirectoryPath($name, $directory);

		$archive = new \ZipArchive();
		$archive->open($zipFile);
		$archive->extractTo($extractDirectory);
		$archive->close();

		if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			static::renameWin($extractDirectory . '/base-' . $branch, $appDirectory);
		} else {
			rename($extractDirectory . '/base-' . $branch, $appDirectory);
		}
		rmdir($tempDirectoryName);
	}

	/**
	 * @param  string $zipFile
	 */
	static public function cleanUp($zipFile)
	{
		@chmod($zipFile, 0777);
		@unlink($zipFile);
	}

	/**
	 * @return string
	 */
	static public function findComposer()
	{
		if(file_exists(getcwd() . DIRECTORY_SEPARATOR . 'composer.phar')) {
			return '"' . PHP_BINARY . '" composer.phar';
		}

		return 'composer';
	}


	static public function renameWin($oldName, $newName)
	{
		if(!rename($oldName, $newName)) {
			if(copy($oldName, $newName)) {
				unlink($oldName);

				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * @param string $name
	 * @param string $directory
	 * @param array $data
	 */
	static public function updateEnvironment(string $name, string $directory, array $data)
	{
		$envPath = realpath($directory . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . '.env');
		print $envPath;
		if(!file_exists($envPath)) {
			$envSamplePath = realpath($directory . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . '.env.example');
			copy($envSamplePath, $envPath);
		}
		$lines   = file($envPath);
		$updated = [];
		foreach($lines as $line) {
			$line = trim($line);
			if(preg_match('/([^=]+)=(.*)/', $line, $matches)) {
				$key = $matches[1];
				if(array_key_exists($key, $data)) {
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


}
