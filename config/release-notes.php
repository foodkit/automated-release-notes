<?php

return [

    'issue_tracker' => [
        'type'     => 'jira',
        'host'     => env('JIRA_URL', 'https://project.atlassian.net'),
        'username' => env('JIRA_USERNAME', 'user'),
        'password' => env('JIRA_PASSWORD', 'secret'),
        'regex'    => env('JIRA_ISSUE_REGEX', '/GT-[\d]+/'),
    ],

];