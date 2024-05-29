<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

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



function deleteSensorIfNotWorking() {
    $sensors = Capsule::table('sensors')->get();

    foreach($sensors as $sensor) {
        if(!$sensor->isOn) {
            Capsule::table('sensors')
                ->where('id', $sensor->id)
                ->delete();
            Capsule::table('malfunctioning_sensors')
                ->where('sensor_id', $sensor->id)
                ->delete();
            Capsule::table('sensor_data')
                ->where('sensor_id', $sensor->id)
                ->delete();
        }
    }
}

//each day
while (true) {
    $start = microtime(true);

    deleteSensorIfNotWorking();

    // Calculate the elapsed time
    $elapsed = microtime(true) - $start;

    // Calculate the remaining time until the next day (24 hours - elapsed time)
    $remainingTime = (24 * 60 * 60) - $elapsed;

    // Sleep for the remaining time to ensure the loop runs every 24 hours
    if ($remainingTime > 0) {
        sleep($remainingTime);
    }
}

