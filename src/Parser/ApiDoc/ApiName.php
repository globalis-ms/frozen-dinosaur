<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * ApiName annotation
 */
class ApiName extends ApiDoc
{

    /**
     * Regex for ApiGroup
     * "name"
     * @var string
     */
    protected $regExp = '/^(?<name>.*)$/';

}
