<?php

namespace LaravelRocket\Installer\Tasks;

use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class BaseTask
{

    /** @var bool */
    protected $verboseMode = false;

    /** @var \Symfony\Component\Console\Input\InputInterface */
    protected $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /** @var \LaravelRocket\Installer\Console\Commands\BaseCommand */
    protected $command;

    /**
     * BaseTask constructor.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \LaravelRocket\Installer\Console\Commands\BaseCommand &$command
     * @param bool $verboseMode
     */
    public function __construct($input, $output, &$command, $verboseMode = false)
    {
        $this->verboseMode = $verboseMode;
        $this->input       = $input;
        $this->output      = $output;
        $this->command     = $command;
    }

    /**
     * @param \LaravelRocket\Installer\Console\Commands\BaseCommand &$command
     */
    public static function arguments(&$command)
    {
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function preprocess($data)
    {
        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function dialog($data)
    {
        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function update($data)
    {
        return $data;
    }

    /**
     * @param string $description
     * @param array $options
     * @param int $default
     * @param bool $multiSelect
     *
     * @return mixed
     */
    protected function askOptions(string $description, array $options, int $default, $multiSelect = false)
    {
        $helper   = $this->command->getHelper('question');
        $question = new ChoiceQuestion(
            $description,
            $options,
            $default
        );
        if ($multiSelect) {
            $question->setMultiselect(true);
        }
        $question->setErrorMessage('Answer is invalid');
        $answer = $helper->ask($this->input, $this->output, $question);

        return $answer;
    }

    /**
     * @param string $description
     * @param bool $default
     *
     * @return bool
     */
    protected function askYesNo(string $description, bool $default)
    {
        $helper   = $this->command->getHelper('question');
        $question = new ConfirmationQuestion($description, $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * @param string $description
     * @param string $default
     *
     * @return string
     */
    protected function askQuestion(string $description, string $default)
    {
        $helper   = $this->command->getHelper('question');
        $question = new Question($description, $default);
        $answer   = $helper->ask($this->input, $this->output, $question);

        return $answer;
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
