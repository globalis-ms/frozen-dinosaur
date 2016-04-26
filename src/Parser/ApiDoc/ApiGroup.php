<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * ApiGroup annotation
 */
class ApiGroup extends ApiDoc
{
    /**
     * Regex for ApiGroup
     * "name"
     * @var string
     */
    protected $regExp = '/^(?<name>.*)$/';
}
