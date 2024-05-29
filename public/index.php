<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Routing\RouteContext;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

//Middleware
$app->addRoutingMiddleware();

$app->addBodyParsingMiddleware();

// CORS Middleware as a closure with correct signature
$app->add(function (Request $request, RequestHandler $handler): Response {
    // Handle OPTIONS request
    if ($request->getMethod() === "OPTIONS") {
        $response = new SlimResponse();
        return $response->withHeader('Access-Control-Allow-Origin', '*')
                        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                        ->withHeader('Access-Control-Allow-Credentials', 'true')
                        ->withStatus(200);
    }

    // Handle other requests
    $response = $handler->handle($request);

    return $response->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->withHeader('Access-Control-Allow-Credentials', 'true');
});

//ErrorHandling
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'db',
    'database' => 'a_tower',
    'username' => 'user',
    'password' => 'password',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '', 
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

//Get all sensors
$app->get('/sensors', function(Request $request, Response $response, $args) {
    $result = Capsule::table('sensors')->get();
    $response->getBody()->write($result->toJson());
    return $response->withHeader('Content-Type', 'application/json');
});

//Add 100 sensors for simulations
$app->post('/add-100-sensors', function(Request $request, Response $response) {
    $sensors = Capsule::table('sensors')->get();
    $count = count($sensors);

    $faces = ['north', 'east', 'south', 'west'];

    if($count <= 10000) {
        for($i = 0; $i < 100; $i++) {
            $key = array_rand($faces);
            Capsule::table('sensors')->insert([
                'face' => $faces[$key],
                'isOn' => false
            ]);
        }
        $response->getBody()->write(json_encode(['message' => 'Sensors added succsefully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    else {
        $response->getBody()->write(json_encode(['error' => 'No place for sensors.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//Add 1 sensor from payload
$app->post('/add-sensor', function(Request $request, Response $response, $args) {
    $sensors = Capsule::table('sensors')->get();
    $count = count($sensors);

    if($count <= 10000) {
        $data = $request->getParsedBody();
        Capsule::table('sensors')->insert([
            'face' => $data['face'],
            'isOn' => false
        ]);
        $response->getBody()->write(json_encode(['message' => 'Sensor added succsefully.']));
        return $response;
    } else {
        $response->getBody()->write(json_encode(['error' => 'No place for sensor.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

//Set sensor data for check
$app->post('/set-sensor-data/{id}', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $sensorId = $args['id'];
    $sensor = Capsule::table('sensors')->where('id', $sensorId)->first();
    
    if (!$sensor) {
        $response->getBody()->write(json_encode(['message' => 'Sensor with id [' . $sensorId . '] not found.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }

    Capsule::table('sensor_data')->insert([
        'timestamp' => date('Y-m-d H:i:s'),
        'sensor_id' => $sensor->id,
        'face' => $sensor->face,
        'temperature' => $data['temperature']
    ]);
    $response->getBody()->write(json_encode(['message' => 'Sensor data was recorded succefully.']));
    return $response->withHeader('Content-Type', 'application/json');
});

//get sensor data for check
$app->get('/get-sensor-data', function(Request $request, Response $response) {
    $result = Capsule::table('sensor_data')->get();
    $response->getBody()->write($result->toJson());
    return $response->withHeader('Content-Type', 'application/json');
});

//Get aggreagated hourly report
$app->get('/reports/hourly-averages', function (Request $request, Response $response, $args) {
    $result = Capsule::table('hourly_averages')
        ->where('hour', '>=', date('Y-m-d H:i:s', strtotime('-1 week')))
        ->get();
    $response->getBody()->write($result->toJson());
    return $response->withHeader('Content-Type', 'application/json');
});

//Get Malfunctioning report
$app->get('/reports/malfunctioning-sensors', function (Request $request, Response $response, $args) {
    $result = Capsule::table('malfunctioning_sensors')->get();
    $response->getBody()->write($result->toJson());
    return $response->withHeader('Content-Type', 'application/json');
});


//Delete sensor by id
$app->delete('/delete-sensor/{id}', function (Request $request, Response $response, $args) {
    $sensorId = $args['id'];
    
    $deletedSensor = Capsule::table('sensors')->where('id', $sensorId)->delete();
    $deletedBadSensor = Capsule::table('malfunctioning_sensors')->where('sensor_id', $sensorId)->delete();
    $deletedBadSensorData = Capsule::table('sensor_data')->where('sensor_id', $sensorId)->delete();

    if($deletedSensor && $deletedBadSensor && $deletedBadSensorData) {
        $response->getBody()->write(json_encode(['message' => 'The sensor with id [' . $sensorId . '] has been deleted succefully.']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['message' => 'The sensor with id [' . $sensorId . '] not found!']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function($req, $res) {
    $handler = $this->notFoundHandler; // handle using the default Slim page not found handler
    return $handler($req, $res);
});

$app->run();