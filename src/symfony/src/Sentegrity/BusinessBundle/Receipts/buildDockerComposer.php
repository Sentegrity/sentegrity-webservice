<?php
/** Receipt: build docker-compose.yml out of docker-compose.yml.dst */
use Symfony\Component\Yaml\Yaml as Yaml;
use Sentegrity\BusinessBundle\Services\Support\ConsoleColor;

$recipe = new \Soy\Recipe();

$recipe->component('docker-compose-parameters', function () {
    // do the magic
    $env = '../../../../../../.local/docker.env.yml';
    $dst = '../../../../../../docker-compose.yml.dst';
    $target = '../../../../../../docker-compose.yml';

    $template = Yaml::parse(file_get_contents($dst));
    $env = Yaml::parse(file_get_contents($env));

    $template['services']['mysql']['ports'][0] = str_replace('&port_mysql&', $env['ports']['mysql'], $template['services']['mysql']['ports'][0]);
    $template['services']['application']['ports'][0] = str_replace('&port_https&', $env['ports']['https'], $template['services']['application']['ports'][0]);

    foreach ($template['services']['application']['volumes'] as &$item) {
        $item = str_replace('&volumes_main&', $env['volumes_main'], $item);
    }

    file_put_contents($target, Yaml::dump($template));
    ConsoleColor::log('Docker compose generated', 'black', false, 'green');
    echo "\n";
});

return $recipe;