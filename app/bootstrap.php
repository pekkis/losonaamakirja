<?php

require_once __DIR__.'/../vendor/autoload.php';

use Knp\Provider\ConsoleServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Application;
use Losofacebook\Service\ImageService;
use Losofacebook\Service\PersonService;
use Losofacebook\Service\PostService;
use Losofacebook\Service\CompanyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Silex\Provider\SessionServiceProvider;

$app = new Silex\Application();

$app['debug'] = true;

// Simulated login
$app['dispatcher']->addListener(KernelEvents::REQUEST, function (KernelEvent $event) use ($app) {
    $app['session']->set('user', array('username' => 'gaylord.lohiposki'));
});

// Providers

$app->register(new ConsoleServiceProvider(), array(
    'console.name'              => 'Losonaamakirja',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__.'/..'
));

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'losofacebook',
        'user' => 'root',
        'password' => 'g04753m135',
        'charset' => 'utf8',
    ),
));

$app->register(
    new SessionServiceProvider(),
    [
        'session.storage.save_path' => __DIR__ . '/data/sessions',
        'session.storage.options' => [
            'name' => 'losofacebook',
        ]
    ]
);

// Services

$app['imageService'] = $app->share(function (Application $app) {
    return new ImageService($app['db'], realpath(__DIR__ . '/data/images'));
});

$app['personService'] = $app->share(function (Application $app) {
    return new PersonService($app['db']);
});

$app['postService'] = $app->share(function (Application $app) {
    return new PostService($app['db'], $app['personService']);
});

$app['companyService'] = $app->share(function (Application $app) {
    return new CompanyService($app['db']);
});


// Controllers

$app->get('/api/person', function(Application $app, Request $request) {

    /** @var PersonService $personService */
    $personService = $app['personService'];

    $params = $request->query->all();

    $persons = $personService->findBy($params, false);

    return new JsonResponse(
        $persons
    );
});


$app->get('/api/person/{username}', function(Application $app, $username) {

    /** @var PersonService $personService */
    $personService = $app['personService'];

    $person = $personService->findByUsername($username);

    return new JsonResponse(
        $person
    );

});

$app->get('/api/person/{username}/friend', function(Application $app, Request $request, $username) {

    /** @var PersonService $personService */
    $personService = $app['personService'];

    $params = $request->query->all();

    return new JsonResponse(
        $personService->findFriendsBy($username, $params)
    );

});


$app->get('/api/post/{personId}', function(Application $app, $personId) {

    /** @var PostService $postService */
    $postService = $app['postService'];

    $posts = $postService->findByPersonId($personId);

    return new JsonResponse(
        $posts
    );

});


$app->post('/api/post/{personId}', function(Application $app, Request $request, $personId) {

    /** @var PostService $postService */
    $postService = $app['postService'];

    $data = json_decode($request->getContent());
    $post = $postService->create($personId, $data);
    return new JsonResponse(
        $post
    );

});

$app->post('/api/post/{postId}/comment', function(Application $app, Request $request, $postId) {

    /** @var PostService $postService */
    $postService = $app['postService'];
    $data = json_decode($request->getContent());
    $comment = $postService->createComment($postId, $data);
    return new JsonResponse(
        $comment
    );

});



$app->get('/api/image/{id}/{version}', function(Application $app, $id, $version = null) {

    /** @var ImageService $imageService */
    $imageService = $app['imageService'];
    $response = $imageService->getImageResponse($id, $version);
    return $response;

})->value('version', null);


$app->get('/api/company', function(Application $app, Request $request) {

    /** @var CompanyService $companyService */
    $companyService = $app['companyService'];

    return new JsonResponse(
        $companyService->findBy([], ['orderBy' => $request->query->get('orderBy', 'name ASC')])
    );

});

$app->get('/api/company/{name}', function(Application $app, $name) {

    /** @var CompanyService $companyService */
    $companyService = $app['companyService'];

    return new JsonResponse(
        $companyService->findByName($name)
    );

});




return $app;
