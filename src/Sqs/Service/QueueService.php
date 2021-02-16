<?php

namespace Sqs\Service;

use Aws\Sqs\SqsClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sqs\Service\Contract\QueueServiceInterface;
use Throwable;

/**
 * Class QueueService
 * @package Sqs\Service
 */
class QueueService implements QueueServiceInterface
{
    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * QueueService constructor.
     * @param SqsClient $sqsClient
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        SqsClient $sqsClient,
        ?LoggerInterface $logger = null
    ) {
        $this->sqsClient = $sqsClient;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getUrl(string $name): ?string
    {
        $params = ['QueueName' => $name];

        try {
            $result = $this->sqsClient->getQueueUrl($params);
            $queueUrl = $result->get('QueueUrl');
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Getting queue url: {$throwable->getMessage()}",
                $params
            );
        }

        return $queueUrl ?? null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function create(string $name): ?string
    {
        $isFifo = substr($name, -5) === '.fifo';
        $params = [
            'QueueName' => $name,
            'Attributes' => [
                'FifoQueue' => $isFifo ? 'true' : 'false',
            ],
        ];

        try {
            $result = $this->sqsClient->createQueue($params);
            $queueUrl = $result->get('QueueUrl');
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Creating queue: {$throwable->getMessage()}",
                $params
            );
        }

        return $queueUrl ?? null;
    }
}
