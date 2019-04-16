<?php
namespace LaravelRocket\Installer\Tasks;

use RuntimeException;

class CheckRequirements extends BaseTask {

	public function preprocess($data)
	{
		if (!class_exists('ZipArchive')) {
			throw new RuntimeException('The Zip PHP extension is not installed. Please install it and try again.');
		}

		return $data;
	}

}
