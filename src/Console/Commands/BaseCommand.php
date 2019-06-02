<?php
namespace LaravelRocket\Installer\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    /** @var \Symfony\Component\Console\Input\InputInterface */
    protected $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /** @var array  */
    protected $tasks = [
    ];

    /**
     * @var \LaravelRocket\Installer\Tasks\BaseTask[]
     */
    protected $taskInstances = [];

    protected function configure()
    {
        foreach ($this->tasks as $task) {
            $task::arguments($this);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $verboseMode = empty($input->getOption('verbose')) ? false : true;
        if ($verboseMode) {
            $this->output("Verbose Mode", "blue");
        }
        foreach ($this->tasks as $task) {
            $this->taskInstances[] = new $task($input, $output, $this, $verboseMode);
        }

        $this->handle();
    }

    protected function handle()
    {
        $data = [];
        foreach ($this->taskInstances as $task) {
            $data = $task->preprocess($data);
        }

        foreach ($this->taskInstances as $task) {
            $data = $task->dialog($data);
        }

        $data = $this->onBeforeUpdate($data);

        foreach ($this->taskInstances as $task) {
            $data = $task->update($data);
        }

        $this->onAfterUpdate($data);
    }

    protected function onBeforeUpdate($data)
    {
        return $data;
    }

    protected function onAfterUpdate($data)
    {
        return $data;
    }

    /**
     * @param string      $message
     * @param string|null $color
     */
    protected function output(string $message, $color = null)
    {
        if (!empty($color)) {
            if (in_array($color, ['info', 'comment', 'error', 'question'])) {
                $this->output->writeln('<'.$color.'>'.$message.'</'.$color.'>');
            } else {
                $this->output->writeln('<fg='.$color.'>'.$message.'</>');
            }
        } else {
            $this->output->writeln($message);
        }
    }

    protected function outputNewLine()
    {
        $this->output->writeln('');
    }
}
