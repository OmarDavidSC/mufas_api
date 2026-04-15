<?php 
    error_reporting(E_ERROR); // E_ALL // E_ERROR
    date_default_timezone_set('America/Lima');
    session_start();

    require_once __DIR__ . "/../vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();

    // use Illuminate\Database\Capsule\Manager as Capsule;
    use Aura\Router\RouterContainer;
    use App\Middlewares\{Application, Authenticate, AuthMiddleware };
    
    // $capsule = new Capsule;

    require_once __DIR__."/../config/database.php";

    $request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES
    );
    
    $routerContainer = new RouterContainer();
    $map = $routerContainer->getMap();

    require_once "../routes/web.php";

    $matcher = $routerContainer->getMatcher();
    $route = $matcher->match($request);
    if (!$route) {
        // get the first of the best-available non-matched routes
        $failedRoute = $matcher->getFailedRoute();
        $filename = '/error/404.twig';
        $status = 404;
        // which matching rule failed?
        switch ($failedRoute->failedRule) {
            case 'Aura\Router\Rule\Allows':
                // 405 METHOD NOT ALLOWED
                // Send the $failedRoute->allows as 'Allow:'
                break;
            case 'Aura\Router\Rule\Accepts':
                // 406 NOT ACCEPTABLE
                break;
            default:
                // 404 NOT FOUND
                $filename = '/error/404.twig';
            break;
        }
        Application::abort($status);
    }

    // $userAgent = $_SERVER['HTTP_USER_AGENT'];
    // echo json_encode($_COOKIE);
    //echo json_encode($_SERVER['HTTP_USER_AGENT']);
    //  echo json_encode(getallheaders()['Origin']);
    // exit;

    $exceptions = [
        "api.auth.signin",
        "api.auth.password.forgot",
        "api.auth.password.verify",
        "api.auth.password.restore",
        "api.auth.signup",
        "api.example.lote"
    ];

    Application::setItem('redirect', $_ENV["URL_CLIENT"]); 
    Application::setItem('company', Authenticate::factory());

    $authMiddleware = new AuthMiddleware();
    $authMiddleware->verifyTokenApi("api", $route, $exceptions);
    $authMiddleware->verifyTokenDeveloper("dev", $route, $exceptions);    

    // add route attributes to the request
    foreach ($route->attributes as $key => $val) {
        $request = $request->withAttribute($key, $val);
    }

    $handlerData = $route->handler;
    $controllerName = $handlerData['Controller'];
    $actionName = $handlerData['Action'];
    $controller = new $controllerName;
    $response = $controller->$actionName($request);
    if (is_object($response)) {
        // emit the response
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }
        echo $response->getBody();
    } else {
        echo json_encode($response);
    }
    exit;

?>
