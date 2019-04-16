<?php
namespace LaravelRocket\Installer\Tests\Services;

use LaravelRocket\Installer\Services\FileService;
use LaravelRocket\Installer\Tests\TestCase;

class FileServiceTest extends TestCase
{
	public function testGetInstance()
	{
		$fileName = $this->getTempFile('.zip');
		FileService::download($fileName);

		$this->assertTrue(file_exists($fileName));

		unlink($fileName);
	}
}
