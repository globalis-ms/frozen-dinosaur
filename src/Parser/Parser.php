<?php

namespace FrozenDinosaur\Parser;

use \ArrayObject;
use TokenReflection\Broker;
use TokenReflection\Broker\Backend;
use TokenReflection\Broker\Backend\Memory;
use TokenReflection\Exception\FileProcessingException;
use TokenReflection\Exception\ParseException;

/**
 * Parser Class.
 */
class Parser
{
    /**
     * Broker instance.
     * @var TokenReflection\Broker
     */
    protected $broker;

    /**
     * Errors list.
     * @var array
     */
    protected $errors = [];

    /**
     * Parsed classes.
     * @var ArrayObject
     */
    protected $classes;

    /**
     * Parsed constant.
     * @var ArrayObject
     */
    protected $constants;

    /**
     * Parsed functions.
     * @var ArrayObject
     */
    protected $functions;

    /**
     * Parsed internal classes.
     * @var ArrayObject
     */
    protected $internalClasses;

    /**
     * Parsed yokenised classes.
     * @var ArrayObject
     */
    protected $tokenizedClasses;

    /**
     * Classes getter.
     *
     * @return ArrayObject
     */
    public function classes()
    {
        return $this->classes;
    }

    /**
     * Constants getter.
     *
     * @return ArrayObject
     */
    public function constants()
    {
        return $this->constants;
    }

    /**
     * Functions getter.
     *
     * @return ArrayObject
     */
    public function functions()
    {
        return $this->functions;
    }

    /**
     * internalClasses getter.
     *
     * @return ArrayObject
     */
    public function internalClasses()
    {
        return $this->internalClasses;
    }

    /**
     * Tokenized Classes getter.
     *
     * @return ArrayObject
     */
    public function tokenizedClasses()
    {
        return $this->tokenizedClasses;
    }

    /**
     * Broker getter.
     *
     * @return TokenReflection\Broker
     */
    public function broker()
    {
        if ($this->broker === null) {
            $this->broker = new Broker(new Memory());
        }
        return $this->broker;
    }

    /**
     * Parse files.
     *
     * @param array $files Path files.
     * @return void
     */
    public function parse(array $files)
    {
        foreach ($files as $file) {
            try {
                $this->broker()->processFile($file->getPathname());
            } catch (ParseException $exception) {
                $this->errors[] = new FileProcessingException([$exception]);
            } catch (FileProcessingException $exception) {
                $this->errors[] = $exception;
            }
        }
        $this->extractBrokerDataForParserResult();
    }

    /**
     * Extract data from broker.
     *
     * @return void
     */
    private function extractBrokerDataForParserResult()
    {
        $allFoundClasses = $this->broker()->getClasses(
            Backend::TOKENIZED_CLASSES | Backend::INTERNAL_CLASSES | Backend::NONEXISTENT_CLASSES
        );
        $this->classes = new ArrayObject($allFoundClasses);
        $this->constants = new ArrayObject($this->broker()->getConstants());
        $this->functions = new ArrayObject($this->broker()->getFunctions());
        $this->internalClasses = new ArrayObject($this->broker()->getClasses(Backend::INTERNAL_CLASSES));
        $this->tokenizedClasses = new ArrayObject($this->broker()->getClasses(Backend::TOKENIZED_CLASSES));

        $this->classes->uksort('strcasecmp');
        $this->constants->uksort('strcasecmp');
        $this->functions->uksort('strcasecmp');
    }
}
