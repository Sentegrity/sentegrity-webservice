<?php

namespace Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters;

use League\CLImate\CLImate;
use Soy\Task\TaskInterface;

class PrepareSymfonyEnvironmentTask implements TaskInterface
{
    const ENV_VAR_SYMFONY = 'SYMFONY_ENV';

    const CLI_ARG_ENV_DEFAULT = 'dev';

    /**
     * @var string
     */
    public static $cliArgSrcFile = 'app/config/parameters.yml.dist';

    /**
     * @var string
     */
    public static $cliArgDestFile = 'app/config/parameters.yml';

    /**
     * @var PrepareEnvironmentTask
     */
    private $prepareEnvironmentTask;

    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @var ParametersTask
     */
    private $parametersTask;

    /**
     * @param PrepareEnvironmentTask $prepareEnvironmentTask
     * @param ParametersTask $parametersTask
     * @param CLImate $climate
     */
    public function __construct(
        PrepareEnvironmentTask $prepareEnvironmentTask,
        ParametersTask $parametersTask,
        CLImate $climate
    ) {
        $this->prepareEnvironmentTask = $prepareEnvironmentTask;
        $this->parametersTask = $parametersTask;
        $this->climate = $climate;
    }

    /**
     * Finds and replaces environment specific parameters into the symfony parameters.yml file
     */
    public function run()
    {
        $this->climate->green('Running ' . self::class);

        if (getenv(self::ENV_VAR_SYMFONY) &&
            ! $this->climate->arguments->get(ParametersTask::CLI_ARG_ENV) &&
            ! $this->parametersTask->getEnv()
        ) {
            $this->parametersTask->setEnv(getenv(self::ENV_VAR_SYMFONY));
            $this->climate->tab()->white(
                'Symfony Environment detected as "' . $this->parametersTask->getEnv() . '"'
            );
        }

        if (! $this->climate->arguments->get(ParametersTask::CLI_ARG_ENV) &&
            ! $this->parametersTask->getEnv()
        ) {
            $this->parametersTask->setEnv(static::CLI_ARG_ENV_DEFAULT);
            $this->climate->tab()->white(
                'Symfony Environment is falling back to default "' . $this->parametersTask->getEnv() . '"'
            );
        }

        $this->prepareEnvironmentTask->setEnclosingParamSymbol('&');
        $this->prepareEnvironmentTask->run();
    }

    /**
     * Since SymfonyTask inherits from EnvironmentTask we are setting
     * the default output file as symfony standard parameters.yml
     * as well for the source file which is parameters.yml.dist
     *
     * @param CLImate $climate
     * @throws \Exception
     */
    public static function prepareCli(CLImate $climate)
    {
        $args = $climate->arguments->all();

        $destFile = $args[PrepareEnvironmentTask::CLI_ARG_DEST_FILE];
        $destFile->setDefaultValue(self::$cliArgDestFile);
        $destFile->setValue(self::$cliArgDestFile);

        $srcFile = $args[PrepareEnvironmentTask::CLI_ARG_SRC_FILE];
        $srcFile->setDefaultValue(self::$cliArgSrcFile);
        $srcFile->setValue(self::$cliArgSrcFile);

        $env = $args[ParametersTask::CLI_ARG_ENV];
        $env->setDefaultValue(static::CLI_ARG_ENV_DEFAULT);
    }
}
