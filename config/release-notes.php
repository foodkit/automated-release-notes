<?php

return [

    'issue_tracker' => [
        'type'     => 'jira',
        'host'     => env('JIRA_URL', 'https://project.atlassian.net'),
        'username' => env('JIRA_USERNAME', 'emran'),
        'password' => env('JIRA_PASSWORD', 'secret'),
    ],

];