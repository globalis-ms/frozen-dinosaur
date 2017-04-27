<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * ApiDefine annotation
 */
class ApiDefine extends ApiDoc
{
    /**
     * Regex for apiDefine
     * "name [title]\n[description]"
     * @var string
     */
    protected $regExp = ' /^(?<name>\w*)(.*?)(?:\s+|$)(.*)$/m';
}
