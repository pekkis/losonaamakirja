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
use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Provider\MonologServiceProvider;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\FirePHPHandler;

$app = new Silex\Application();
$app['debug'] = true;

// Simulated login
$app['dispatcher']->addListener(KernelEvents::REQUEST, function (KernelEvent $event) use ($app) {
    $app['session']->set('user', array('username' => 'gaylord.lohiposki'));

    // $logger = new Doctrine\DBAL\Logging\EchoSQLLogger();
    // $conn->getConfiguration()->setSQLLogger($logger);

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
        'password' => 'userpassu',
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


$app['memcached'] = $app->share(function (Application $app) {
    $m = new Memcached();
    $m->addServer('localhost', 11211);
    // $m->setOption(Memcached::OPT_COMPRESSION, false);
    return $m;
});

$app['personService'] = $app->share(function (Application $app) {
    return new PersonService($app['db'], $app['memcached']);
});

$app['imageService'] = $app->share(function (Application $app) {
    return new ImageService(
        $app['db'],
        realpath(__DIR__ . '/data/images'),
        $app['memcached']
    );
});


$app['postService'] = $app->share(function (Application $app) {

    return new PostService(
        $app['db'],
        $app['personService'],
        $app['memcached']
    );

});

$app['companyService'] = $app->share(function (Application $app) {
    return new CompanyService(
        $app['db'],
        $app['memcached']
    );
});


// Controllers

$app->get('/api/person', function(Application $app, Request $request) {

    /** @var PersonService $personService */
    $personService = $app['personService'];

    $params = $request->query->all();

    /* Great and totally unsafe kludge for like searches :) */
    foreach ($params as $key => $value) {
        if (preg_match('/%/', $value)) {
            $params[$key] = function (QueryBuilder $qb) use($key, $value) {
                $qb->andWhere($qb->expr()->like($key, $qb->expr()->literal($value)));
            };
        }
    }
    $persons = $personService->findBy($params,  [], false);

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


$app->get('/api/post/{personId}', function(Application $app, Request $request, $personId) {

    /** @var PostService $postService */
    $postService = $app['postService'];


    // $posts = $postService->findByPersonId($personId);

    $page = $request->query->get('page', 1);
    $limit = $request->query->get('limit', 10);

    $posts = $postService->findBy(
        [
            'poster_id' => $personId,
        ],
        [
            'orderBy' => ['date_created DESC'],
            'page' => $page,
            'limit' => $limit,
        ]
    );

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

$imageRenderer = function(Application $app, $id, $version = null) {
    /** @var ImageService $imageService */
    $imageService = $app['imageService'];
    $response = $imageService->getImageResponse($id, $version);
    return $response;
};

// Did not like the original url, so whe changed.
$app->get('/api/image/{id}/{version}', $imageRenderer)->value('version', null);
$app->get('/api/image-renderer/{id}/{version}', $imageRenderer)->value('version', null);

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

/*
$app->register(
    new MonologServiceProvider(),[]
);

$app['monolog.handler'] = function () use ($app) {
    return new ChromePHPHandler($app['monolog.level']);
};
if ( $app['debug'] ) {

    $logger = new Doctrine\DBAL\Logging\DebugStack();

    $app['db.config']->setSQLLogger($logger);

    $app->after(function(Request $request, Response $response) use ($app, $logger) {
        $queries = array_slice($logger->queries, sizeof($logger->queries) - 100);
        foreach ($queries as $query) {
            $app['monolog']->debug($query['sql']);
        }
    });
}
*/

return $app;
