<?php

namespace FoodKit\ReleaseNote;

class IssueTrackerFactory
{
    public static function create($type, $config)
    {
        $trackerClass = '\\FoodKit\\ReleaseNote\\IssueTracker\\'.ucfirst($type);

        if (!class_exists($trackerClass)) {
            throw new \InvalidArgumentException("The specified issue tacker $type does not exist.");
        }

        return new $trackerClass($config);
    }
}