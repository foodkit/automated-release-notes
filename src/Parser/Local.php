<?php

namespace Foodkit\ReleaseNote\Parser;

use Foodkit\ReleaseNote\Agent\AgentInterface;

class Local implements ParserInterface
{
    const SERVICE_TYPE_GITHUB = 'github';
    const SERVICE_TYPE_BITBUCKET = 'bitbucket';

    private $agent;
    private $hosts;

    /**
     * Local constructor.
     * @param AgentInterface $agent
     * @param array $hosts
     */
    public function __construct(AgentInterface $agent, $hosts)
    {
        $this->agent = $agent;
        $this->hosts = $hosts;
    }

    /**
     * @param string $start
     * @param string $end
     * @return array
     */
    public function getCommits($start, $end)
    {
        $command = "git log $start...$end --oneline";
        $notes = $this->agent->execute($command);
        $commits = [];

        if ($notes) {
            $commits = explode(PHP_EOL, $notes);
        }

        return $commits;
    }

    /**
     * @param string $start
     * @param string $end
     * @return null|string
     */
    public function getCompareUrl($start, $end)
    {
        $command = "git config --get remote.origin.url";
        $origin = $this->agent->execute($command);

        foreach ($this->hosts as $remoteHost => $serviceType) {
            if (strpos($origin, $remoteHost) !== false) {

                $matched = preg_match(
                    "/^((ssh:\/\/)?(git@)?|https:\/\/)(?<host>[\w.]+)(:[0-9]+)?(:|\/)(?<owner>[\w-_]+)\/(?<project>[\w-_]+).git$/",
                    $origin, $originStructure);

                $compareUrl = $matched ? $this->getCompareUrlByType($originStructure, $serviceType, $start,
                    $end) : null;

                return $compareUrl;
            }
        }

        return null;
    }

    /**
     * @param array $originStructure
     * @param string $type
     * @param string $start
     * @param string $end
     * @return null|string
     */
    private function getCompareUrlByType($originStructure, $type, $start, $end)
    {
        switch ($type) {
            case self::SERVICE_TYPE_GITHUB:
                return "https://{$originStructure['host']}/{$originStructure['owner']}/{$originStructure['project']}/compare/$start...$end";
            case self::SERVICE_TYPE_BITBUCKET:
                return "https://{$originStructure['host']}/projects/{$originStructure['owner']}/repos/{$originStructure['project']}/compare/diff?targetBranch=refs%2Ftags%2F$start&sourceBranch=refs%2Ftags%2F$end";
        }

        return null;
    }
}
