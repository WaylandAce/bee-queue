<?php
/** @noinspection PhpUnhandledExceptionInspection */

use Dynatech\Libraries\BeeQueue\Job;
use Dynatech\Libraries\BeeQueue\Queue;

require '../vendor/autoload.php';

try {

    $queue = new Queue('queueName', [
        'redis' => [
            'host' => '127.0.0.1',
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
