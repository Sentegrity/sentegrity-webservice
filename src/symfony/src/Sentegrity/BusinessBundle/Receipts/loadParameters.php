<?php

use Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters\ParametersTask;
use Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters\PrepareEnvironmentTask;
use Sentegrity\BusinessBundle\Services\SoySymfonyBuildParameters\PrepareSymfonyEnvironmentTask;

// set paths
ParametersTask::$environmentFilenameMask = 'environment.yml';
ParametersTask::$environmentFilePath = '../../../../../../.local';
PrepareSymfonyEnvironmentTask::$cliArgSrcFile = '../../../../app/config/parameters.yml.dist';
PrepareSymfonyEnvironmentTask::$cliArgDestFile = '../../../../app/config/parameters.yml';

$recipe = new \Soy\Recipe();

$recipe->component('symfony-parameters', function (PrepareSymfonyEnvironmentTask $environmentTask) {
    $environmentTask
        ->run();
})
    ->cli([ParametersTask::class, 'prepareCli'])
    ->cli([PrepareEnvironmentTask::class, 'prepareCli'])
    ->cli([PrepareSymfonyEnvironmentTask::class, 'prepareCli'])
;

return $recipe;