<?php

namespace FoodKit\ReleaseNote\IssueTracker;

interface IssueTrackerInterface
{
    public function __construct($config = []);
    public function getIssueRegex();
    public function getIssueURL($identifier);
    public function getIssueSummary($identifier);
}

