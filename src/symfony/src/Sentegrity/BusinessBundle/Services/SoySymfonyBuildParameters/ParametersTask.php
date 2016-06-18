<?php

namespace Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters;

use League\CLImate\CLImate;
use Soy\Task\TaskInterface;
use Symfony\Component\Yaml\Yaml;

class ParametersTask implements TaskInterface
{
    const CLI_ARG_ENV = 'env';

    const CLI_ARG_ENV_PATH = 'env-path';

    const CLI_ARG_ENV_FILE = 'env-file';

    const CLI_ARG_GLOBAL_FILE = 'global-file';

    /**
     * @var string
     */
    public static $environmentFilenameMask = 'environment.%s.yml';

    /**
     * @var string
     */
    public static $environmentFilePath = 'files/environment';

    /**
     * @var string
     */
    private $env;

    /**
     * @var string
     */
    private $envFile;

    /**
     * @var string
     */
    private $globalEnvFile;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @param CLImate $climate
     */
    public function __construct(CLImate $climate)
    {
        $this->climate = $climate;
    }

    /**
     * Fetch the parameters form a given Yaml file and prepare them for further use
     */
    public function run()
    {
        $this->climate->green('Running ' . self::class);

        $forcedEnvFile = $this->climate->arguments->get(static::CLI_ARG_ENV_FILE);
        if ($forcedEnvFile && ! $this->getEnvFile()) {
            $this->setEnvFile($forcedEnvFile);
        }

        if (! $this->getEnv()) {
            $this->setEnv($this->climate->arguments->get(static::CLI_ARG_ENV));
        }

        if (! $this->getEnvFile()) {
            $this->setEnvFile($this->getEnvFilename());
        }

        if (! file_exists($this->getEnvFile())) {
            $this->climate->tab()->red('Unable to read file: ' . $this->getEnvFile());
            die(21);
        }

        $this->climate->tab()->white('Read environment file ' . $this->getEnvFile());
        $envParams = $this->readParamsFromFile($this->getEnvFile());

        $forcedGlobalFile = $this->climate->arguments->get(static::CLI_ARG_GLOBAL_FILE);
        if ($forcedGlobalFile && ! $this->getGlobalEnvFile()) {
            $this->setGlobalEnvFile($forcedGlobalFile);
        }

        if (! $this->getGlobalEnvFile()) {
            $this->setGlobalEnvFile($this->getEnvFilename('global'));
        }

        if (! file_exists($this->getGlobalEnvFile())) {
            $this->climate->tab()->yellow(
                'Global file not found or not readable, proceeding without it. Tried file: ' . $this->getGlobalEnvFile()
            );
        }

        $distEnvironmentParameters = [];
        if (file_exists($this->getGlobalEnvFile())) {
            $this->climate->tab()->white('Read global environment file ' . $this->getGlobalEnvFile());
            $distEnvironmentParameters = $this->readParamsFromFile($this->getGlobalEnvFile());
        }

        if (is_array($distEnvironmentParameters)) {
            $envParams = array_merge($distEnvironmentParameters, $envParams);
        }

        $this->parameters = $envParams;
    }

    public static function prepareCli(CLImate $climate)
    {
        $climate->arguments->add([
            self::CLI_ARG_ENV_PATH => [
                'longPrefix' => self::CLI_ARG_ENV_PATH,
                'description' => 'The directory which contains the env files',
                'defaultValue' => self::$environmentFilePath,
                'required' => false,
            ],
        ]);

        $climate->arguments->add([
            self::CLI_ARG_ENV => [
                'longPrefix' => self::CLI_ARG_ENV,
                'description' => 'The current environment name. I.E.: dev, test, prod',
                'required' => false,
            ],
        ]);

        $climate->arguments->add([
            self::CLI_ARG_ENV_FILE => [
                'longPrefix' => self::CLI_ARG_ENV_FILE,
                'description' => sprintf(
                    'This option will ignore both "%s" and "%s" in order to force the file to use',
                    static::CLI_ARG_ENV,
                    static::CLI_ARG_ENV_PATH
                ),
                'required' => false,
            ],
        ]);

        $climate->arguments->add([
            self::CLI_ARG_GLOBAL_FILE => [
                'longPrefix' => self::CLI_ARG_GLOBAL_FILE,
                'description' => 'This option will force the global file to be used',
                'required' => false,
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param string $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * @return string
     */
    public function getEnvFile()
    {
        return $this->envFile;
    }

    /**
     * @param string $envFile
     */
    public function setEnvFile($envFile)
    {
        $this->envFile = $envFile;
    }

    /**
     * @return string
     */
    public function getGlobalEnvFile()
    {
        return $this->globalEnvFile;
    }

    /**
     * @param string $globalEnvFile
     * @return $this
     */
    public function setGlobalEnvFile($globalEnvFile)
    {
        $this->globalEnvFile = $globalEnvFile;

        return $this;
    }

    /**
     * @param string $env
     * @return string
     */
    public function getEnvFilename($env = null)
    {
        $args = $this->climate->arguments;

        if ($env === null) {
            $env = $this->getEnv();
        }

        $path = $args->get(static::CLI_ARG_ENV_PATH);

        return sprintf($path . '/' . self::$environmentFilenameMask, $env);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $filename
     * @return array
     */
    private function readParamsFromFile($filename)
    {
        $environmentContents = file_get_contents($filename);

        return Yaml::parse($environmentContents);
    }
}
