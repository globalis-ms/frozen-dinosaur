<?php

namespace FrozenDinosaur\Parser;

/**
 * ApiDoc Anotation Parser
 */
abstract class ApiDoc
{
    /**
     * regExp to use.
     * @var string
     */
    protected $regExp;

    /**
     * Parsing function.
     *
     * @param string $string The string to parse.
     * @return array
     */
    public function parse($string)
    {
        preg_match($this->regExp, $string, $match);
        return $match;
    }
}
