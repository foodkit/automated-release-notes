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

        $notes = "# Release notes - $end\n\n";

        foreach ($summaries as $key => $summary) {
            $url = $this->issueTracker->getIssueURL($key);
            $notes .= "* [$key]($url) - $summary\n";
        }

        return $notes;
    }
}
