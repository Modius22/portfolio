<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/resources',
));

$app['twig']->addFunction(new Twig_Function('resource', function ($name, $embed = false, $base64 = true) use ($app) {
    $globals = $app['twig']->getGlobals();
    $resources = $globals['resources'];
    $result = $resources[$name];
    if (substr($result, 0, 1) == '@') {
        $result = 'resources/' . substr($result, 1);
    }
    if ($embed === true) {
        $result = file_get_contents($result);
        if ($base64 === true) {
            $result = 'data:;base64,' . base64_encode($result);
        }
    }
    return $result;
}));

$app['twig']->addFilter(new Twig_Filter('values', function ($array) {
    return array_values($array);
}));


$app->get('/', function () use ($app) {
    $jsonFile = __DIR__ . '/resources/meta.json';
    $templateData = json_decode(file_get_contents($jsonFile), true);
    $res = [];
    if (isset($templateData['resources'])) {
        $res = $templateData['resources'];
    }
    $app['twig']->addGlobal('resources', $res);

    foreach ($templateData['sections'] as $sectionName => $section) {
        if (isset($section['description']) && !isset($section['template'])) {
            $templateData['sections'][$sectionName]['description'] = $app['twig']->createTemplate($section['description'])->render($templateData);
        }
    }

    $templateFile = 'template.html.twig';
    if (is_file(__DIR__ . '/resources/template.custom.html.twig')) {
        $templateFile = 'template.custom.html.twig';
    }
    return $app['twig']->render($templateFile, $templateData);
});

$app->run();