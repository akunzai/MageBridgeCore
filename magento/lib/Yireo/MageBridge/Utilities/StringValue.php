<?php

/**
 * MageBridge.
 *
 * @author Yireo
 * @copyright Copyright 2017
 * @license Open Source License
 *
 * @link https://www.yireo.com
 */

namespace Yireo\MageBridge\Utilities;

/**
 * Class resembling common string actions.
 */
class StringValue
{
    /**
     * @var string
     */
    private $string;

    /**
     * StringValue constructor.
     */
    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        $partsArray = [];
        $parts = explode(',', $this->string);
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            $partsArray[] = $part;
        }

        return $partsArray;
    }
}
