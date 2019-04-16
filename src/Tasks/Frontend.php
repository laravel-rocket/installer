<?php
namespace LaravelRocket\Installer\Tasks;

use LaravelRocket\Installer\Services\FileService;
use LaravelRocket\Installer\Services\ProcessService;
use RuntimeException;

class Frontend extends BaseTask
{
    public function dialog($data)
    {
        $hasYarn = ProcessService::checkCommandExists('yarn');
        $hasNpm = ProcessService::checkCommandExists('npm');

        if (!$hasNpm && !$hasYarn) {
            throw new RuntimeException('Need to install npm or yarn for frontend building');
        }

        $data['nodePackageManager'] = $hasNpm ? 'npm' : 'yarn';

        if ($hasNpm && $hasYarn) {
            $useYarn = $this->askQuestion('Do you use yarn instead of npm ? ( Y/n ) ', "Y");
            if (strlen($useYarn) > 0 &&  substr(strtolower($useYarn), 0, 1) === 'y') {
                $data['nodePackageManager'] = 'yarn';
            }
        }

        return $data;
    }

    public function update($data)
    {
        FileService::updateEnvironment($data['appName'], $data['appDirectory'], [
            'DB_HOST'     => $data['nodePackageManager'],
        ]);

        ProcessService::run($data['nodePackageManager'] . ' install', $data['appName'], $data['appDirectory'], $this->verboseMode, $this->output);

        return $data;
    }
}
