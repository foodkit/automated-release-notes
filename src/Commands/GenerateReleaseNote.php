<?php

namespace FoodKit\ReleaseNote\Commands;

use Illuminate\Console\Command;
use FoodKit\ReleaseNote\IssueTrackerFactory;
use FoodKit\ReleaseNote\Service\ReleaseNoteGenerator;
use FoodKit\ReleaseNote\IssueTracker\IssueTrackerInterface;
use Symfony\Component\Console\Input\InputOption;

class GenerateReleaseNote extends Command
{
    /** @var string */
    protected $name = 'release-note:generate';

    /** @var string */
    protected $description = 'Generate a release note given two tags/branches.';

    /** @var string */
    protected $tracker = 'jira';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if (!$start = $this->input->getOption('start')) {
            $start = $this->ask('Start Tag/Branch:');
        }

        if (!$end = $this->input->getOption('end')) {
            $end = $this->ask('End Tag/Branch:');
        }

        $config = config("release-notes.issue_tracker");

        /** @var IssueTrackerInterface $tracker */
        $issueTracker = IssueTrackerFactory::create($config['type'], $config);

        /** @var ReleaseNoteGenerator $generator */
        $generator = new ReleaseNoteGenerator($issueTracker);

        $this->output->writeln($generator->generate($start, $end));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['start', null, InputOption::VALUE_OPTIONAL, 'The start tag/branch'],
            ['end', null, InputOption::VALUE_OPTIONAL, 'The end tag/branch'],
        ];
    }
}
