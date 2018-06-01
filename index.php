<?php


use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;


require_once __DIR__ . '/vendor/autoload.php';

define('DEBUG', true);

$twigLoader = new Twig\Loader\FilesystemLoader(__DIR__ . '/resources');
$twig = new Twig\Environment($twigLoader, [
    'debug' => DEBUG
]);

$mailer = new Swift_Mailer(new Swift_Transport_NullTransport(new Swift_Events_SimpleEventDispatcher()));

$twig->addFunction(new Twig_Function('resource', function ($name, $embed = false, $base64 = true) use ($twig) {
    $globals = $twig->getGlobals();
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

$twig->addFilter(new Twig_Filter('values', function ($array) {
    return array_values($array);
}));


$routes = new RouteCollection();
$routes->add('index', new Route('/', [
    '_controller' => function () use ($twig) {
        $jsonFile = __DIR__ . '/resources/meta.json';
        $templateData = json_decode(file_get_contents($jsonFile), true);
        $res = [];
        if (isset($templateData['resources'])) {
            $res = $templateData['resources'];
        }
        $twig->addGlobal('resources', $res);

        foreach ($templateData['sections'] as $sectionName => $section) {
            if (isset($section['description']) && !isset($section['template'])) {
                $templateData['sections'][$sectionName]['description'] = $twig->createTemplate($section['description'])->render($templateData);
            }
        }

        $templateFile = 'template.html.twig';
        if (is_file(__DIR__ . '/resources/template.custom.html.twig')) {
            $templateFile = 'template.custom.html.twig';
        }
        return new Response($twig->render($templateFile, $templateData));
    }
]));

/** @var Request $request */
$request = Request::createFromGlobals();
$routes->add('contact', new Route('/contact', [
    '_controller' => function () use ($request, $twig, $mailer) {

        $form = Forms::createFormFactory()->createBuilder()
            ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class)
            ->add('message', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class)
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest();

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $messagebody = $data['message'];
                $name = $data['name'];
                $subject = "Message from " . $name;
                $message = new Swift_Message($subject, $twig->render('sections/email.html.twig',   // email template
                    array('name' => $name,
                        'message' => $messagebody,
                    )), 'text/html');
                $message
                    ->setFrom(array('hans@someone.tld'))// replace with your own
                    ->setTo(array('foo@bar.tld'));// replace with email recipient
                var_dump($mailer->send($message));
            }
            return new Response($twig->render('sections/email.html.twig', array(
                'message' => 'Message Sent',
                'form' => $form->createView()
            )));
        }
        return new Response($twig->render('sections/email.html.twig', array(
                'message' => 'Send message to us',
                'form' => $form->createView()
            )
        ));
    }
]));

$context = new RequestContext();
$context->fromRequest($request);

$matcher = new UrlMatcher($routes, $context);
$parameters = $matcher->match($request->getPathInfo());

/** @var \Symfony\Component\HttpFoundation\Response $resp */
$resp = call_user_func($parameters['_controller']);
$resp->send();
