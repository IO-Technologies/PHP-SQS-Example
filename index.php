<?php

require_once __DIR__ . '/vendor/autoload.php';

use Aws\Sqs\SqsClient;
use Command\ExampleCommand;
use Log\SimpleLogger;
use Sqs\Service\MessengerService;
use Sqs\Service\QueueService;

try {
    $sqsClient = new SqsClient(
        [
            'region' => '{{ enter your region }}',
            'version' => '{{ enter your version }}',
        ]
    );
    $logger = new SimpleLogger();
    $code = (new ExampleCommand(
        new QueueService($sqsClient, $logger),
        new MessengerService($sqsClient, $logger),
        $logger
    ))->execute();

    $logger->info("Return command code: {$code}");
} catch (Throwable $throwable) {
    echo $throwable->getMessage() . PHP_EOL;
}
