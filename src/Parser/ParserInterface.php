<?php

namespace Foodkit\ReleaseNote\Parser;

interface ParserInterface
{
    public function getCommits($start, $end);
    public function getCompareUrl($start, $end);
}
