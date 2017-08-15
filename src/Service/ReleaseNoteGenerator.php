<?php

namespace FoodKit\ReleaseNote\Service;

use FoodKit\ReleaseNote\IssueTracker\IssueTrackerInterface;

class ReleaseNoteGenerator
{
    const FORMAT_GITHUB = 'github';
    const FORMAT_SLACK = 'slack';

    /** @var IssueTrackerInterface */
    protected $issueTracker;

    /** @var string */
    private $format;

    public function __construct(IssueTrackerInterface $issueTracker, $format = self::FORMAT_GITHUB)
    {
        $this->issueTracker = $issueTracker;
        $this->format = $format;
    }

    public function generate($start, $end)
    {
        $command = "git log $start...$end --oneline";
        $notes  = shell_exec($command);
        $commits = explode(PHP_EOL, $notes);

        $tickets = [];
        foreach ($commits as $commit) {
            if (preg_match($this->issueTracker->getIssueRegex(), $commit, $matches)) {
                $ticket = $matches[0];
                if (!in_array($ticket, $tickets)) {
                    $tickets[] = $ticket;
                }
            }
        }

        $summaries = [];
        foreach ($tickets as $ticket) {
            if ($summary = $this->issueTracker->getIssueSummary($ticket)) {
                $summaries[$ticket] = $summary;
            }
        }

        if (count($summaries) == 0) {
            return 'No referenced issue found.';
        }

        $items = [];

        foreach ($summaries as $key => $summary) {
            $items[] = [
                'key' => $key,
                'url' => $this->issueTracker->getIssueURL($key),
                'summary' => $summary
            ];
        }

        $compareUrl = $this->getCompareUrl($start, $end);

        return $this->format($end, $items, $compareUrl);
    }

    private function getCompareUrl($start, $end)
    {
        $command = "git config --get remote.origin.url";
        $origin = trim(shell_exec($command));

        if (strpos($origin, 'github.com') !== false) {

            if (strpos($origin, 'git@') !== false) {
                $origin = str_replace('git@github.com:', 'https://github.com/', $origin);
            }

            $origin = str_replace('.git', '', $origin);
            $compareUrl = $origin . '/compare/' . $start . '...' . $end;

            return $compareUrl;
        }

        return null;
    }

    private function format($release, $items, $compareUrl)
    {
        switch ($this->format) {

            case self::FORMAT_GITHUB:

                $notes = "# Release notes - $release\n\n";

                foreach ($items as $item) {
                    $notes .= "* [{$item['key']}]({$item['url']}) - {$item['summary']}\n";
                }

                if (!empty($compareUrl)) {
                    $notes .= "\n[See release commits]($compareUrl)";
                }

                return $notes;

            case self::FORMAT_SLACK:

                $notes = "";

                foreach ($items as $item) {
                    $notes .= "<{$item['url']}|{$item['key']}> - {$item['summary']}\n";
                }

                if (!empty($compareUrl)) {
                    $notes .= "\n<$compareUrl|See release commits>";
                }

                return $notes;

        }
    }
}
