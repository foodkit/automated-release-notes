<?php

namespace Foodkit\ReleaseNote\Commands;

use Foodkit\ReleaseNote\IssueTrackerFactory;
use Foodkit\ReleaseNote\ParserFactory;
use Foodkit\ReleaseNote\Service\ReleaseNoteGenerator;
use Foodkit\ReleaseNote\IssueTracker\IssueTrackerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Foodkit\ReleaseNote\Parser\Local;

class GenerateReleaseNoteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate a release note given two tags/branches.')
            ->addOption('start', null, InputOption::VALUE_OPTIONAL, 'The start tag/branch')
            ->addOption('end', null, InputOption::VALUE_OPTIONAL, 'The end tag/branch')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Issue tracker type', 'jira')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Issue tracker host (ex: https://project.atlassian.net)')
            ->addOption('user', null, InputOption::VALUE_OPTIONAL, 'Issue tracker username')
            ->addOption('pass', null, InputOption::VALUE_OPTIONAL, 'Issue tracker password')
            ->addOption('regex', null, InputOption::VALUE_OPTIONAL, 'Issue prefix regex')
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Output format (github/slack/json)', 'github')
            ->addOption('hosts', null, InputOption::VALUE_OPTIONAL, 'Custom remote git hosts and their service type(Ex github.local:github,bitbucket.local:bitbucket)', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$start = $input->getOption('start')) {
            $question = new Question('Start Tag/Branch: ');
            $start    = $helper->ask($input, $output, $question);
        }

        if (!$end = $input->getOption('end')) {
            $question = new Question('End Tag/Branch: ');
            $end      = $helper->ask($input, $output, $question);
        }

        $config = $this->getConfig($input);

        /** @var IssueTrackerInterface $tracker */
        $issueTracker = IssueTrackerFactory::create($config['type'], $config);

        $parser = ParserFactory::create('local', $config['hosts']);

        $generator = new ReleaseNoteGenerator($issueTracker, $parser, $config['format']);

        $output->writeln($generator->generate($start, $end));
    }

    private function getConfig(InputInterface $input)
    {
        $dotenv = new \Dotenv\Dotenv(getcwd());
        $dotenv->load();

        $config = [
            'type'     => 'jira',
            'format'   => 'github',
            'host'     => getenv('JIRA_URL'),
            'username' => getenv('JIRA_USERNAME'),
            'password' => getenv('JIRA_PASSWORD'),
            'regex'    => getenv('JIRA_ISSUE_REGEX'),
            'hosts'    => $this->extractHosts(getenv('GIT_CUSTOM_HOSTS'))
        ];

        if ($input->getOption('type')) {
            $config['type'] = $input->getOption('type');
        }

        if ($input->getOption('host')) {
            $config['host'] = $input->getOption('host');
        }

        if ($input->getOption('user')) {
            $config['username'] = $input->getOption('user');
        }

        if ($input->getOption('pass')) {
            $config['password'] = $input->getOption('pass');
        }

        if ($input->getOption('regex')) {
            $config['regex'] = $input->getOption('regex');
        }

        if ($input->getOption('format')) {
            $config['format'] = $input->getOption('format');
        }

        if ($input->getOption('hosts')) {
            $config['hosts'] = $this->extractHosts($input->getOption('hosts'));
        }



        foreach (['type', 'format', 'host', 'regex'] as $key => $value) {
            if (empty($value)) {
                throw new RuntimeException("The required parameter '$key' is not configured.");
            }
        }

        return $config;
    }

    /**
     * @param array $hostsConfig
     * @return array
     */
    private function extractHosts($hostsConfig)
    {
        $hosts = [
            'github.com' => Local::SERVICE_TYPE_GITHUB,
            'bitbucket.org' => Local::SERVICE_TYPE_BITBUCKET
        ];

        if(empty($hostsConfig)) {
            return $hosts;
        }

        $hosts = explode(',', $hostsConfig);
        foreach ($hosts as $host){
            $hostData = explode(':', $host);
            $hosts[$hostData[0]] = $hostData[1];
        }

        return $hosts;
    }
}
