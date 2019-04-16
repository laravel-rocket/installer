<?php
namespace LaravelRocket\Installer\Tasks;

use LaravelRocket\Installer\Services\FileService;

class UnitTest extends BaseTask
{
    public function preprocess($data)
    {
        return $data;
    }

    public function dialog($data)
    {
        return $data;
    }

    public function update($data)
    {
        FileService::updatePhpUnitXml($data['appName'], $data['appDirectory'], [
            'DB_DATABASE' => $data['databaseName'] . '_test',
        ]);

        return $data;
    }
}
