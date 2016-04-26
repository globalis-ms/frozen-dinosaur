<?php

namespace FrozenDinosaur\Template;

use FrozenDinosaur\Parser\Parser;

class Swagger
{
    protected $build;

    /**
     * Parser
     * @var FrozenDinosaur\Parser\Parser
     */
    protected $parser;

    /**
     * Constructor
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function build()
    {

    }
}
