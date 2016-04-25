<?php

namespace FrozenDinosaur\Console\Command;

use FrozenDinosaur\Finder\Finder;
use FrozenDinosaur\Parser\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * Configure function.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('generate')
             ->setDescription('Generate API unit tests')
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
                 'Target dir for documentation.'
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
        try {
            $this->scanAndParse(
                $input->getOption('source'),
                $input->getOption('exclude'),
                $input->getOption('extensions')
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
     * Scan and parse sources folders.
     *
     * @param array $source     Dossiers à scanner.
     * @param array $exclude    Dossiers à ne pas scanner.
     * @param array $extensions Extensions des fichiers à scanner.
     * @return void
     */
    private function scanAndParse(array $source, array $exclude, array $extensions)
    {
        $this->io->writeln('<info>Scanning sources and parsing</info>');
        $files = $this->finder()->find($source, $exclude, $extensions);
        $this->parser()->parse($files);
        foreach ($this->parser()->classes() as $class) {
            foreach ($class->getOwnMethods() as $method) {
                if ($annotations = $method->getAnnotations()) {
                    // Parse doc block.
                    var_dump($annotations);
                }
            }
        }

        /*
            API DOC ANNOATIONS:
            @api {method} path [title]
            @apiDefine name [title]
                                 [description]
            @apiDescription text
            @apiError [(group)] [{type}] field [description]
            @apiErrorExample [{type}] [title]
                             example
            @apiExample [{type}] title
                     example
            @apiGroup name
            @apiHeader [(group)] [{type}] [field=defaultValue] [description]
            @apiHeaderExample [{type}] [title]
                               example
            @apiIgnore [hint] //Ignore api
            @apiName name
            @apiParam [(group)] [{type}] [field=defaultValue] [description]
            @apiParamExample [{type}] [title]
                               example
            @apiPermission name
            @apiSampleRequest url
            @apiSuccess [(group)] [{type}] field [description]
            @apiSuccessExample [{type}] [title]
                               example
            @apiUse name
            @apiVersion version
            $this->parser()->parse($files);
                        $this->reportParserErrors($this->parser->getErrors());
                        $stats = $this->parserResult->getDocumentedStats();
                        $this->io->writeln(sprintf(
                        'Found <comment>%d classes</comment>, <comment>%d constants</comment> and <comment>%d functions</comment>',
                        $stats['classes'],
                        $stats['constants'],
                        $stats['functions']
                    ));
        */

    }
}
