<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/resources',
));


$app['twig']->addFunction(new Twig_Function('resource', function ($name) use ($app) {
    $globals = $app['twig']->getGlobals();
    $resources = $globals['resources'];
    return $resources[$name];
}));

$app['twig']->addFilter(new Twig_Filter('values', function ($array) {
    return array_values($array);
}));


$app->get('/', function () use ($app) {
    $jsonFile = __DIR__ . '/resources/meta.json';
    $templateData = json_decode(file_get_contents($jsonFile), true);
    $app['twig']->addGlobal('resources', $templateData['resources']);

    return $app['twig']->render('template.html.twig', $templateData);
});

$app->run();