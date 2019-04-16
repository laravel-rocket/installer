<?php

namespace LaravelRocket\Installer\Services;

class TemplateService
{
	/**
	 * @param  array $data
	 * @param  string $filePath
	 */
	static public function replace($data, $filePath)
	{
		$original = file_get_contents($filePath);

		foreach ($data as $key => $value) {
			$templateKey = '%%' . strtoupper($key) . '%%';
			$original    = str_replace($templateKey, $value, $original);
		}

		file_put_contents($filePath, $original);
	}
}
