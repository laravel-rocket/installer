<?php
namespace LaravelRocket\Installer\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    /** @var \Symfony\Component\Console\Input\InputInterface */
    protected $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $this->handle();
    }

    protected function handle()
    {
    }
    /**
     * @param string $description
     * @param array  $options
     * @param int    $default
     * @param bool   $multiSelect
     *
     * @return mixed
     */
    protected function askOptions(string $description, array $options, int $default, $multiSelect = false)
    {
        $helper   = $this->getHelper('question');
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
     * @param bool   $default
     *
     * @return bool
     */
    protected function askYesNo(string $description, boolean $default)
    {
        $helper   = $this->getHelper('question');
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
        $helper   = $this->getHelper('question');
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
}
