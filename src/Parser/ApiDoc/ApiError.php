<?php

namespace FrozenDinosaur\Parser\ApiDoc;

use FrozenDinosaur\Parser\ApiDoc;

/**
 * ApiError annotation
 */
class ApiError extends ApiDoc
{

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->regExp = '/^'
            // [(group)].
            . '\s*(?:\(\s*(?<group>.+?)\s*\)\s*)?'
            // [{type}].
            . '\s*(?:\{\s*(?<type>[a-zA-Z0-9\(\)#:\.\/\\\[\]_-]+)'
                . '\s*(?:\{\s*(?<size>.+?)\s*\}\s*)?'
                . '\s*(?:=\s*(?<allowed_values>.+?)(?=\s*\}\s*))?'
            . '\s*\}\s*)?'
            // [field=defaultValue].
            . '(\[?\s*'
                . '(?<name>[a-zA-Z0-9\:\.\/\\_-]+(?:\[[a-zA-Z0-9\.\/\\_-]*\])?)'
                . '(?:\s*=\s*(?<default_value>'
                // With double quote.
                . '"([^"]*)"'
                // With quote.
                . '|\'([^\']*)\''
                // Without quote.
                .'|(.*?)(?:\s|\]|$)'
                .'))?'
            . '\s*\]?\s*)'
            // [description].
            . '(?<description>.*)?'
            . '$/';
    }
}
