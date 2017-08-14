<?php

namespace FoodKit\ReleaseNote\Service;

use FoodKit\ReleaseNote\IssueTracker\IssueTrackerInterface;

class ReleaseNoteGenerator
{
    protected $issueTracker;

    public function __construct(IssueTrackerInterface $issueTracker)
    {
        $this->issueTracker = $issueTracker;
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

        $notes = "# Release notes - $end\n\n";

        foreach ($summaries as $key => $summary) {
            $url = $this->issueTracker->getIssueURL($key);
            $notes .= "* [$key]($url) - $summary\n";
        }

        if ($compareUrl = $this->getCompareUrl($start, $end)) {
            $notes .= "\n[Compare]($compareUrl)";
        }

        return $notes;
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
}
