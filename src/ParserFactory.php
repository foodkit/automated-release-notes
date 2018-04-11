<?php

namespace Foodkit\ReleaseNote;

use Foodkit\ReleaseNote\Parser\ParserInterface;
use Foodkit\ReleaseNote\Agent\Shell;

class ParserFactory
{
    /**
     * @param string $type
     * @param array $hosts
     * @return ParserInterface
     */
    public static function create($type, $hosts)
    {
        $parserClass = '\\Foodkit\\ReleaseNote\\Parser\\'.ucfirst($type);

        if (!class_exists($parserClass)) {
            throw new \InvalidArgumentException("No available parser could be identified for repo.");
        }

        $agent = new Shell();
        $parser = new $parserClass($agent, $hosts);

        return $parser;
    }
}
