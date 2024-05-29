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

function setSensorData () {
    $min = 0.0;
    $max = 45.0;
    $sensors = Capsule::table('sensors')->get();

    foreach($sensors as $sensor) {
        $randomTemperature = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        Capsule::table('sensor_data')->insert([
            'timestamp' => date('Y-m-d H:i:s'),
            'sensor_id' => $sensor->id,
            'face' => $sensor->face,
            'temperature' => $randomTemperature
        ]);

        Capsule::table('sensors')
            ->where('id', $sensor->id)
            ->update(['isOn' => true]);
    }
}

function aggregateHourlyData() {
    $faces = ['north', 'east', 'south', 'west'];
    $hour = date('Y-m-d H:00:00', strtotime('-1 hour'));

    foreach($faces as $face) {
        $average = Capsule::table('sensor_data')
            ->where('face', $face)
            ->where('timestamp', '>=', $hour)
            // ->where('timestamp', '<', date('Y-m-d H:i:s', strtotime($hour . ' +1 hour')))  //If it means exactly for 1 hour so uncomment the line but shoul wait for 1 hour that programm runs
            ->avg('temperature');
        if($average) {
            $facesInDb = Capsule::table('hourly_averages')->get();
            $flag = false;
            foreach($facesInDb as $faceInDb) {
                if($face == $faceInDb->face) {
                    Capsule::table('hourly_averages')
                        ->where('id', $faceInDb->id)
                        ->update([
                            'hour' => $hour,
                            'average_temperature' => $average
                        ]);
                    $flag = true;
                }
            }
            if(!$flag) {
                Capsule::table('hourly_averages')->insert([
                    'hour' => $hour,
                    'face' => $face,
                    'average_temperature' => $average
                ]);
            }
            
        }
    }
}

function detectMalfunctioningSensors() {
    $faces = ['north', 'east', 'south', 'west'];

    foreach ($faces as $face) {
        $average = Capsule::table('sensor_data')
            ->where('face', $face)
            ->where('timestamp', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->avg('temperature');

        $deviation = 0.20 * $average;

        $sensors = Capsule::table('sensor_data')
            ->select('sensor_id', Capsule::raw('AVG(temperature) as avg_temp'))
            ->where('face', $face)
            ->where('timestamp', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->groupBy('sensor_id')
            ->having('avg_temp', '>', $average + $deviation)
            ->orHaving('avg_temp', '<', $average - $deviation)
            ->get();

        foreach($sensors as $sensor) {
            $badSensors = Capsule::table('malfunctioning_sensors')->get();
            $flag = true;
            foreach($badSensors as $badSensor) {
                if($sensor->sensor_id == $badSensor->sensor_id) {
                    $flag = false;
                }
            }
            if($flag) {
                Capsule::table('malfunctioning_sensors')->insert([
                    'sensor_id' => $sensor->sensor_id,
                    'average_temperature' => $sensor->avg_temp,
                    'face' => $face,
                ]);
            }
        }
    }
}




while (true) {
    $start = microtime(true);

    
    setSensorData();
    aggregateHourlyData();
    detectMalfunctioningSensors();

    // Calculate the elapsed time
    $elapsed = microtime(true) - $start;

    // Sleep for the remaining time to ensure the loop runs every second
    if ($elapsed < 1) {
        usleep((1 - $elapsed) * 1000000);
    }
}
