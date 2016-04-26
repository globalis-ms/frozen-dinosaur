<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * ApiDescription annotation
 */
class ApiDescription extends ApiDoc
{
    /**
     * Regex for ApiDescription
     * "text"
     * @var string
     */
    protected $regExp = '/^(?<text>.*)$/';
}
