<?php
/** @noinspection PhpUnhandledExceptionInspection */

use Dynatech\Libraries\BeeQueue\Job;
use Dynatech\Libraries\BeeQueue\Queue;

require '../vendor/autoload.php';

try {

    $queue = new Queue('chrome-render-queue', [
        'redis' => [
            'host' => 'redis.sandbox.dyninno.net',
            'port' => 6379
        ]
    ]);

    $job = $queue->createJob([
        'test' => 'data'
    ]);

    $job->save(function (Job $job) {
        echo "Saved!: " . $job->getId(). PHP_EOL;
    });

} catch (Throwable $e) {
    var_dump($e);
}
