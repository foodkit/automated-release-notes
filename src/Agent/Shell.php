<?php

namespace Foodkit\ReleaseNote\Agent;

class Shell implements AgentInterface
{

    /**
     * @param string $payload
     * @return string
     */
    public function execute($payload)
    {
        return trim(shell_exec($payload));
    }
}
