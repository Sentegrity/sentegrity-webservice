<?php

namespace Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters;

use League\CLImate\CLImate;
use Soy\Replace\ReplaceTask;
use Soy\Task\TaskInterface;

class PrepareEnvironmentTask implements TaskInterface
{
    const CLI_ARG_DEST_FILE = 'dest-file';

    const CLI_ARG_SRC_FILE = 'src-file';

    /**
     * @var ParametersTask
     */
    private $parametersTask;

    /**
     * @var ReplaceTask
     */
    private $replaceTask;

    /**
     * @var string
     */
    private $sourceFile;

    /**
     * @var string
     */
    private $destinationFile;

    /**
     * @var string
     */
    private $enclosingSymbol = '';

    /**
     * @var string
     */
    private $envFile;

    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @param ParametersTask $parametersTask
     * @param ReplaceTask $replaceTask
     * @param CLImate $CLImate
     */
    public function __construct(ParametersTask $parametersTask, ReplaceTask $replaceTask, CLImate $CLImate)
    {
        $this->parametersTask = $parametersTask;
        $this->replaceTask = $replaceTask;
        $this->climate = $CLImate;
    }

    /**
     * Replace the parameters set in envfile with placeholders in the source file
     * and write them to destination file
     */
    public function run()
    {
        $this->climate->green('Running ' . self::class);

        if (! $this->destinationFile) {
            $this->destinationFile = $this->climate->arguments->get(self::CLI_ARG_DEST_FILE);
        }

        if (! $this->sourceFile) {
            $this->sourceFile = $this->climate->arguments->get(self::CLI_ARG_SRC_FILE);
        }

        if ($this->sourceFile === null || $this->destinationFile === null) {
            $exceptionMessage = sprintf('Please provide a source and destination file for %s', self::class);
            throw new \RuntimeException($exceptionMessage);
        }

        $this->climate->tab()->white('Template file ' . $this->sourceFile);
        if (! file_exists($this->sourceFile)) {
            $this->climate->tab()->red('Unable to read file: ' . $this->sourceFile);
            die(22);
        }
        $this->climate->tab()->white('Destination file ' . $this->destinationFile);
        if (file_exists($this->destinationFile)) {
            $this->climate->tab()->yellow('Destination file will be replaced');
        }

        $this->parametersTask->run();

        $replacements = [];
        foreach ($this->parametersTask->getParameters() as $key => $value) {
            $key = $this->enclosingSymbol . $key . $this->enclosingSymbol;

            $replacements[$key] = $value;
        }

        $this->replaceTask
            ->setReplacements($replacements)
            ->setSource($this->sourceFile)
            ->setDestination($this->destinationFile);

        $this->replaceTask->run();

        if (file_exists($this->destinationFile)) {
            $this->climate->bold()->blue($this->destinationFile . ' file generated successfully');
        }
    }

    /**
     * When linked as callback for Soy's prepare, adds a command line argument for this task.
     *
     * @param CLImate $climate
     * @return CLImate
     * @throws \Exception
     */
    public static function prepareCli(CLImate $climate)
    {
        $climate->arguments->add([
            self::CLI_ARG_DEST_FILE => [
                'longPrefix' => self::CLI_ARG_DEST_FILE,
                'description' => 'The destination file',
                'required' => false,
            ],
        ]);

        $climate->arguments->add([
            self::CLI_ARG_SRC_FILE => [
                'longPrefix' => self::CLI_ARG_SRC_FILE,
                'description' => 'The source file used as template for generating the destination file',
                'required' => false,
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getSourceFile()
    {
        return $this->sourceFile;
    }

    /**
     * @param string $sourceFile
     * @return self
     */
    public function setSourceFile($sourceFile)
    {
        $this->sourceFile = $sourceFile;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestinationFile()
    {
        return $this->destinationFile;
    }

    /**
     * @param string $destinationFile
     * @return self
     */
    public function setDestinationFile($destinationFile)
    {
        $this->destinationFile = $destinationFile;

        return $this;
    }

    /**
     * @param string $enclosingSymbol
     * @return self
     */
    public function setEnclosingParamSymbol($enclosingSymbol)
    {
        $this->enclosingSymbol = $enclosingSymbol;

        return $this;
    }

    /**
     * @param string $envFile
     * @return self
     */
    public function setEnvFile($envFile)
    {
        $this->envFile = $envFile;

        return $this;
    }
}
