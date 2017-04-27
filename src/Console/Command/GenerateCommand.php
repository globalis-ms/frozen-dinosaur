<?php

namespace FrozenDinosaur\Console\Command;

use FrozenDinosaur\Finder\Finder;
use FrozenDinosaur\Parser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generation CLI Command.
 */
class GenerateCommand extends Command
{

    /**
     * File Finder instance.
     * @var FrozenDinosaur\Finder\Finder
     */
    protected $finder;

    /**
     * Perser Instance.
     * @var FrozenDinosaur\Parser\Parser
     */
    protected $parser;

    /**
     * Output file name
     * @var string
     */
    protected $destinationFileName;

    /**
     * Input sources
     * @var array
     */
    protected $sources;

    /**
     * Api Name Parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiName
     */
    protected $parserApiName;

    // protected Api informations

    /**
     * Api Parser
     * @var \FrozenDinosaur\Parser\ApiDoc\Api
     */
    protected $parserApi;
    //protected  'apiDefine'        => "name [title]\n[description]",

    /**
     * Api Description Parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiDescription
     */
    protected $parserApiDescription;

    /**
     * Api error parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiError
     */
    protected $parserApiError;
    // protected  'apiErrorExample'  => "[{type}] [title]\nexample",
    // protected  'apiExample'       => "[{type}] title\nexample",

    /**
     * Api Group Parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiGroup
     */
    protected $parserApiGroup;

    /**
     * Api header parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiHeader
     */
    protected $parserApiHeader;
    //protected  'apiHeaderExample' => "[{type}] [title]\nexample",
    //protected  Ignore api.
    //protected  'apiIgnore' =>  "[hint]",
    /**
     * Api Param parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiParam
     */
    protected $parserApiParam;
    // protected  'apiParamExample' =>  "[{type}] [title]\nexample",
    // protected  "apiPermission" =>  "name",
    // protected  "apiSampleRequest" =>  "url",

    /**
     * Api Success parser
     * @var \FrozenDinosaur\Parser\ApiDoc\ApiSuccess
     */
    protected $parserApiSucces;
    //protected  "apiSuccessExample" =>  "[{type}] [title]\nexample",

    /**
     * Configure function.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('generate')
             ->setDescription('Generate API unit tests')
             ->addArgument(
                 'title',
                 InputArgument::REQUIRED,
                 'API title.'
             )
             ->addOption(
                 'source',
                 's',
                 InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                 'Dirs or files documentation is generated for.'
             )
             ->addOption(
                 'destination',
                 'd',
                 InputOption::VALUE_REQUIRED,
                 'Target dir for documentation.',
                 'swagger.json'
             )
             ->addOption(
                 'exclude',
                 null,
                 InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                 'Directories and files matching this mask will not be parsed (e.g. */tests/*).',
                 []
             )
             ->addOption(
                 'extensions',
                 null,
                 InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                 'Scanned file extensions.',
                 ['php']
             )
             ->addOption(
                 'host',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'API host.',
                 'localhost'
             )
             ->addOption(
                 'base-path',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'API base path.',
                 '/'
             )

             ->addOption(
                 'minified',
                 'm',
                 InputOption::VALUE_NONE,
                 'Minfiied format.'
             );
    }

    /**
     * Files Finder Getter.
     *
     * @return FrozenDinosaur\Finder\Finder
     */
    protected function finder()
    {
        if ($this->finder === null) {
            $this->finder = new Finder();
        }
        return $this->finder;
    }

    /**
     * Parser Getter
     *
     * @return FrozenDinosaur\Parser\Parser
     */
    protected function parser()
    {
        if ($this->parser === null) {
            $this->parser = new Parser();
        }
        return $this->parser;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     * @return int 0 if everything went fine, or an error code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = $output;
        $this->destinationFileName = $input->getOption('destination');
        $this->sources = $input->getOption('source');
        $this->jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (!$input->getOption('minified')) {
            $this->jsonOptions = $this->jsonOptions | JSON_PRETTY_PRINT;
        }

        $this->initParsers();
        try {
            $this->scanAndParse(
                $input->getOption('exclude'),
                $input->getOption('extensions'),
                $input->getOption('host'),
                $input->getOption('base-path'),
                $input->getArgument('title')
            );
            // $this->generate($options);
            return 0;
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(PHP_EOL . '<error>%s</error>', $e->getMessage())
            );
            return 1;
        }
    }

    /**
     * Parsers init
     *
     * @return void
     */
    protected function initParsers()
    {
        $this->parserApiName = new \FrozenDinosaur\Parser\ApiDoc\ApiName();

        $this->parserApi = new \FrozenDinosaur\Parser\ApiDoc\Api();
        //  'apiDefine'        = "name [title]\n[description]",
        $this->parserApiDescription = new \FrozenDinosaur\Parser\ApiDoc\ApiDescription();
        $this->parserApiError = new \FrozenDinosaur\Parser\ApiDoc\ApiError();
        //   'apiErrorExample'  = "[{type}] [title]\nexample",
        //   'apiExample'       = "[{type}] title\nexample",
        $this->parserApiGroup = new \FrozenDinosaur\Parser\ApiDoc\ApiGroup();
        $this->parserApiHeader = new \FrozenDinosaur\Parser\ApiDoc\ApiHeader();
        //  'apiHeaderExample' = "[{type}] [title]\nexample",
        //  Ignore api.
        //  'apiIgnore' =  "[hint]",
        $this->parserApiParam = new \FrozenDinosaur\Parser\ApiDoc\ApiParam();
        //   'apiParamExample' =  "[{type}] [title]\nexample",
        //   "apiPermission" =  "name",
        //   "apiSampleRequest" =  "url",
        $this->parserApiSuccess = new \FrozenDinosaur\Parser\ApiDoc\ApiSuccess();
        //protected  "apiSuccessExample" =  "[{type}] [title]\nexample",
    }
    /**
     * Scan and parse sources folders
     *
     * @param array $source     Dossiers à scanner
     * @param array $exclude    Dossiers à ne pas scanner
     * @param array $extensions Extensions des fichiers à scanner
     * @return void
     */
    private function scanAndParse(array $exclude, array $extensions, $host, $base_path, $title)
    {
        $this->io->writeln('<info>Scanning sources and parsing</info>');
        $files = $this->finder()->find($this->sources, $exclude, $extensions);
        $this->parser()->parse($files);

        $extractApiData = [
            "swagger" => "2.0",
            "host" => $host,
            "info" => [
                "version" => "1.0.0",
                "title" => $title,
            ],
            "basePath" => $base_path,
            "schemes" => [
                "http"
            ],
            "consumes" => [
                "application/json"
            ],
            "produces" => [
                "application/json"
            ],
            'paths' => [],
        ];

        foreach ($this->parser()->classes() as $class) {
            foreach ($class->getOwnMethods() as $method) {
                //Check for api definition
                if ($method->hasAnnotation('api')) {
                    $properties = [];
                    $parameters = [];

                    $reBuild = [];

                    $toParse = $method->getAnnotation('api')[0];
                    $parsedAnnotation = $this->parserApi->parse($toParse);
                    $apiMethod = $parsedAnnotation['method'];
                    $apiPath = $parsedAnnotation['path'];
                    if (preg_match_all('/\{(?<params>[^\}]+)\}/', $apiPath, $matches) && !empty($matches['params'])) {
                        foreach ($matches['params'] as $param) {
                            $parameters[] = [
                                'name' => $param,
                                'type' => 'string',
                                "in" => 'path',
                                'required' => true
                            ];
                        }
                    }

                    if (preg_match_all('/\[(?<params>[^\}]+)\]/', $apiPath, $matches) && !empty($matches['params'])) {
                        foreach ($matches['params'] as $param) {
                            $parameters[] = [
                                'name' => $param,
                                'type' => 'string',
                                "in" => 'path',
                                'required' => true
                            ];
                            $apiPath = str_replace('['.$param.']', '{'.$param.'}', $apiPath);
                            $reBuild[] = $param;
                        }
                    }

                    if (!isset($extractApiData['paths'][$apiPath])) {
                        $extractApiData['paths'][$apiPath] = [];
                    }
                    $extractApiData['paths'][$apiPath][$apiMethod] = [];

                    if (!empty($parsedAnnotation['title'])) {
                        $extractApiData['paths'][$apiPath][$apiMethod]['summary'] = $parsedAnnotation['title'];
                    }


                    //ApiName
                    if ($method->hasAnnotation('apiName')) {
                        $tmp = $method->getAnnotation('apiName')[0];
                        $tmp = $this->parserApiName->parse($tmp);
                        $extractApiData['paths'][$apiPath][$apiMethod]['operationId'] = $tmp['name'];
                    }

                    // Description
                    if ($method->hasAnnotation('apiDescription')) {
                        $extractApiData['paths'][$apiPath][$apiMethod]["description"] = '';
                        foreach ($method->getAnnotation('apiDescription') as $parsedParam) {
                             $parsedAnnotation = $this->parserApiDescription->parse($parsedParam);
                             $extractApiData['paths'][$apiPath][$apiMethod]["description"] .= $parsedAnnotation['text'];
                        }
                    }

                    if ($method->hasAnnotation('apiParam')) {
                        $requiredFields = [];
                        if ($apiMethod == 'get') {
                            foreach ($method->getAnnotation('apiParam') as $parsedParam) {
                                $parsedParam = $this->parserApiParam->parse($parsedParam);

                                $param = [
                                    'name' => $parsedParam['name'],
                                    'type' => strtolower($parsedParam['type']),
                                    "in" => 'query',
                                ];

                                if (!preg_match('/^\[.*\]$/', trim($parsedParam[5]))) {
                                    $requiredFields[] = $parsedParam['name'];
                                }

                                switch ($param['type']) {
                                    case 'decimal':
                                    case 'float':
                                        $param['type'] = 'number';
                                        $param['format'] = 'float';
                                        break;
                                    case 'double':
                                        $param['type'] = 'number';
                                        $param['format'] = 'float';
                                        break;
                                    case 'timestamp':
                                        $param['type'] = 'integer';
                                        break;
                                    case 'date':
                                        $param['type'] = 'string';
                                        $param['format'] = 'date';
                                        break;
                                    case 'datetime':
                                        $param['type'] = 'string';
                                        $param['format'] = 'date-time';
                                        break;
                                    case 'text':
                                        $param['type'] = 'string';
                                        break;
                                    case 'binary':
                                        $param['type'] = 'string';
                                        $param['format'] = 'byte';
                                        break;
                                }

                                if (!empty($parsedParam['allowed_values'])) {
                                    $param['enum'] = explode(',', $parsedParam['allowed_values']);
                                }

                                if (!empty($parsedParam['description'])) {
                                    $param['description'] = $parsedParam['description'];
                                }

                                $parameters[] = $param;
                            }
                        } else {
                            $param = $this->buildSchema($method->getAnnotation('apiParam'));
                            $param['name'] = 'body';
                            $param['in'] = 'body';
                            $param['required'] = true;
                            $parameters[] = $param;
                        }
                    }

                    if (!empty($parameters)) {
                        $extractApiData['paths'][$apiPath][$apiMethod]["parameters"] = $parameters;
                    }


                    $extractApiData['paths'][$apiPath][$apiMethod]["responses"] = [];

                    if ($method->hasAnnotation('apiSuccess')) {
                        foreach ($method->getAnnotation('apiSuccess') as $parsedParam) {
                            $parsedParam = $this->parserApiSuccess->parse($parsedParam);
                            if (empty($parsedParam['group'])) {
                                $parsedParam['group'] = '200';
                            }
                            if (!isset($properties[$parsedParam['group']])) {
                                $properties[$parsedParam['group']] = [];
                            }
                            if ($parsedParam['group'] == '204') {
                                $properties[$parsedParam['group']]['description'] = 'Delete';
                            } else {
                                if (!empty($parsedParam['name'])) {
                                    if (!empty($parsedParam['type'])) {
                                        switch ($parsedParam['type']) {
                                            case 'decimal':
                                            case 'float':
                                                $parsedParam['type'] = 'number';
                                                $properties[$parsedParam['group']][$parsedParam['name']]['format'] = 'float';
                                                break;
                                            case 'double':
                                                $parsedParam['type'] = 'number';
                                                $properties[$parsedParam['group']][$parsedParam['name']]['format'] = 'float';
                                                break;
                                            case 'timestamp':
                                                $parsedParam['type'] = 'integer';
                                                break;
                                            case 'date':
                                                $parsedParam['type'] = 'string';
                                                $properties[$parsedParam['group']][$parsedParam['name']]['format'] = 'date';
                                                break;
                                            case 'datetime':
                                                $parsedParam['type'] = 'string';
                                                $properties[$parsedParam['group']][$parsedParam['name']]['format'] = 'date-time';
                                                break;
                                            case 'text':
                                                $parsedParam['type'] = 'string';
                                                break;
                                            case 'binary':
                                                $parsedParam['type'] = 'string';
                                                break;
                                        }
                                        $properties[$parsedParam['group']][$parsedParam['name']]['type'] = $parsedParam['type'];
                                    }
                                    if (!empty($parsedParam['description'])) {
                                        $properties[$parsedParam['group']][$parsedParam['name']]['description'] = $parsedParam['description'];
                                    }
                                }
                            }
                        }
                    }

                    foreach ($properties as $name => $fields) {
                        if ($name != 204) {
                            $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$name] = [
                                //'type' => 'Response ' . $name, // @TODO get real type
                                'schema' => [
                                    "type" => "object",
                                    'properties' => [],
                                ],
                                'description' => '', //@TODO
                            ];

                            foreach ($fields as $fieldName => $field) {
                                $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$name]['schema']['properties'][$fieldName] = $field;
                            }
                        } else {
                             $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$name] = $fields;
                        }
                    }


                    $headers = [];
                    if ($method->hasAnnotation('apiHeader')) {
                        foreach ($method->getAnnotation('apiHeader') as $parsedParam) {
                            $parsedParam = $this->parserApiSuccess->parse($parsedParam);
                            if (empty($parsedParam['group'])) {
                                $parsedParam['group'] = '200';
                            }
                            if (!isset($headers[$parsedParam['group']])) {
                                $headers[$parsedParam['group']] = [];
                            }

                            if (!empty($parsedParam['name'])) {
                                if (!empty($parsedParam['type'])) {
                                    $headers[$parsedParam['group']][$parsedParam['name']]['type'] = $parsedParam['type'];
                                    switch ($parsedParam['type']) {
                                        case 'decimal':
                                        case 'float':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'number';
                                            $headers[$parsedParam['group']][$parsedParam['name']]['format'] = 'float';
                                            break;
                                        case 'double':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'number';
                                            $headers[$parsedParam['group']][$parsedParam['name']]['format'] = 'float';
                                            break;
                                        case 'timestamp':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'integer';
                                            break;
                                        case 'date':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'string';
                                            $headers[$parsedParam['group']][$parsedParam['name']]['format'] = 'date';
                                            break;
                                        case 'datetime':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'string';
                                            $headers[$parsedParam['group']][$parsedParam['name']]['format'] = 'date-time';
                                            break;
                                        case 'text':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'string';
                                            break;
                                        case 'binary':
                                            $headers[$parsedParam['group']][$parsedParam['name']]['type'] = 'string';
                                            $headers[$parsedParam['group']][$parsedParam['name']]['format'] = 'byte';
                                            break;
                                    }

                                    if (!empty($parsedParam['allowed_values'])) {
                                        $headers[$parsedParam['group']][$parsedParam['name']]['enum'] = explode(',', $parsedParam['allowed_values']);
                                    }
                                }
                                if (!empty($parsedParam['description'])) {
                                    $headers[$parsedParam['group']][$parsedParam['name']]['description'] = $parsedParam['description'];
                                }
                            }
                        }
                    }

                    foreach ($headers as $name => $fields) {
                        $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$name]['headers'] = [];
                        foreach ($fields as $fieldName => $field) {
                            $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$name]['headers'][$fieldName] = $field;
                        }
                    }


                    if ($method->hasAnnotation('apiError')) {
                        foreach ($method->getAnnotation('apiError') as $parsedParam) {
                            $parsedParam = $this->parserApiError->parse($parsedParam);
                            $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$parsedParam['type']] = [];

                            if (!empty($parsedParam['description'])) {
                                $extractApiData['paths'][$apiPath][$apiMethod]["responses"][$parsedParam['type']]['description'] = $parsedParam['description'];
                            }
                        }
                    }
                    // Read tag
                    if ($method->hasAnnotation('apiGroup')) {
                        $extractApiData['paths'][$apiPath][$apiMethod]['tags'] = [];
                        foreach ($method->getAnnotation('apiGroup') as $toParse) {
                            $parsedAnnotation = $this->parserApiGroup->parse($toParse);
                            $extractApiData['paths'][$apiPath][$apiMethod]['tags'][] = ucfirst($parsedAnnotation['name']);
                        }
                    }

                    //Rebuild for optionnal get
                    foreach (array_reverse($reBuild) as $optionnalParam) {
                        $newApiPath = preg_replace('/\/\{'.preg_quote($optionnalParam, '/').'}.*/', '', $apiPath);
                        if (!isset($extractApiData['paths'][$newApiPath])) {
                            $extractApiData['paths'][$newApiPath] = [];
                        }
                        $extractApiData['paths'][$newApiPath][$apiMethod] = $extractApiData['paths'][$apiPath][$apiMethod];
                        // Delete param
                        foreach ($extractApiData['paths'][$newApiPath][$apiMethod]["parameters"] as $key => $params) {
                            if ($params['in'] === 'path' && $params['name'] === $optionnalParam) {
                                unset($extractApiData['paths'][$newApiPath][$apiMethod]["parameters"][$key]);
                            }
                        }
                    }
                }
            }
        }

        $this->io->writeln('<info>Write in file "' . $this->destinationFileName . '"</info>');
        file_put_contents($this->destinationFileName, json_encode($extractApiData, $this->jsonOptions));
    }

    protected function buildSchema(array $parsedParams)
    {
            $params = [
                'schema' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ];

            $requiredFields = [];

            foreach ($parsedParams as $parsedParam) {
                $parsedParam = $this->parserApiParam->parse($parsedParam);
                $param = [
                    'type' => strtolower($parsedParam['type'])
                ];

                if (!preg_match('/^\[.*\]$/', trim($parsedParam[5]))) {
                    $requiredFields[] = $parsedParam['name'];
                }


                switch ($param['type']) {
                    case 'decimal':
                    case 'float':
                        $param['type'] = 'number';
                        $param['format'] = 'float';
                        break;
                    case 'double':
                        $param['type'] = 'number';
                        $param['format'] = 'float';
                        break;
                    case 'timestamp':
                        $param['type'] = 'integer';
                        break;
                    case 'date':
                        $param['type'] = 'string';
                        $param['format'] = 'date';
                        break;
                    case 'datetime':
                        $param['type'] = 'string';
                        $param['format'] = 'date-time';
                        break;
                    case 'text':
                        $param['type'] = 'string';
                        break;
                    case 'binary':
                        $param['type'] = 'string';
                        $param['format'] = 'byte';
                        break;
                }

                if (!empty($parsedParam['allowed_values'])) {
                    $param['enum'] = explode(',', $parsedParam['allowed_values']);
                }

                if (!empty($parsedParam['description'])) {
                    $param['description'] = $parsedParam['description'];
                }

                $params['schema']['properties'][$parsedParam['name']] = $param;
            }

            if (!empty($requiredFields)) {
                $params['schema']['required'] = $requiredFields;
            }

            return $params;
    }
}
