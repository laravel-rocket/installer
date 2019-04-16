<?php
namespace LaravelRocket\Installer\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected function getTempFile($extension)
	{
		return tempnam(sys_get_temp_dir(), '') . '.' . $extension;
	}
}
