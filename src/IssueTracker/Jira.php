<?php

namespace Foodkit\ReleaseNote\IssueTracker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Jira implements IssueTrackerInterface
{
    private $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function getIssueRegex()
    {
        return $this->config['regex'];
    }

    public function getIssueURL($identifier)
    {
        return $this->config['host'].'/browse/'.$identifier;
    }

    public function getIssueSummary($identifier)
    {
        $client = new Client();

        try {

            $uri = $this->config['host'].'/rest/api/2/issue/'.$identifier.'?fields=summary';
            $options['headers']['Content-Type'] = 'application/json';
            if (!empty($this->config['username'])) {
                $options['headers']['Authorization'] = $this->generateAuthToken();
            }

            $response = $client->get($uri, $options);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data) {
                return $data['fields']['summary'];
            }

        } catch (ClientException $e) {

            if ($e->getCode() === 404) {

                return null;

            } else {

                throw $e;

            }
        }

        return null;
    }

    /**
     * @return string
     */
    private function generateAuthToken()
    {
        return 'Basic '.base64_encode($this->config['username'].':'.$this->config['password']);
    }
}
