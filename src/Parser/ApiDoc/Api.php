<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * Api annotation
 */
class Api extends ApiDoc
{
    /**
     * Regex for api
     * "{method} path [title]"
     * @var string
     */
    protected $regExp = '/^(?:(?:\{(?<method>.+?)\})?\s*)?(?<path>.+?)(?:\s+(?<title>.+?))?$/';
}
