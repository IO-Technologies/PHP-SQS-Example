<?php

namespace Sqs\Service;

use Aws\Sqs\SqsClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sqs\Model\Contract\MessageInterface;
use Sqs\Model\Message;
use Sqs\Service\Contract\MessengerServiceInterface;
use Throwable;

/**
 * Class MessengerService
 * @package Sqs\Service
 */
class MessengerService implements MessengerServiceInterface
{
    /**
     * Max messages for one batch
     */
    private const MAX_MESSAGES = 10;

    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MessengerService constructor.
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
     * @param string $queueUrl
     * @param int $maxMessages
     * @return MessageInterface[]
     */
    public function receive(string $queueUrl, int $maxMessages = 1): array
    {
        $messages = [];
        $params = [
            'AttributeNames' => [
                'MessageGroupId',
                'MessageDeduplicationId'
            ],
            'MaxNumberOfMessages' => $maxMessages,
            'WaitTimeSeconds' => 0,
            'QueueUrl' => $queueUrl,
        ];

        try {
            $result = $this->sqsClient->receiveMessage($params);

            foreach ($result->get('Messages') ?? [] as $item) {
                $messages[] = new Message(
                    $item['Body'] ?? '',
                    $item['MessageId'] ?? null,
                    $item['Attributes']['MessageGroupId'] ?? null,
                    $item['Attributes']['MessageDeduplicationId'] ?? null,
                    $item['ReceiptHandle'] ?? null
                );
            }

            $count = count($messages);
            $this->logger->debug(
                "$count/$maxMessages successfully received messages"
            );
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Receive messages: {$throwable->getMessage()}",
                $params
            );
        }

        return $messages;
    }

    /**
     * @param string $queueUrl
     * @param MessageInterface $message
     */
    public function send(string $queueUrl, MessageInterface $message): void
    {
        $params = $this->formatSendEntry($message, false) +
            [
                'DelaySeconds' => 0,
                'QueueUrl' => $queueUrl,
            ];

        try {
            $result = $this->sqsClient->sendMessage($params);
            $count = $result->get('MessageId') ? 1 : 0;
            $this->logger->debug("$count/1 successfully sent messages");
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Send message: {$throwable->getMessage()}",
                $params
            );
        }
    }

    /**
     * @param string $queueUrl
     * @param MessageInterface[] $messages
     */
    public function sendBatch(string $queueUrl, array $messages): void
    {
        $params = [];

        try {
            foreach (array_chunk($messages, self::MAX_MESSAGES) as $entries) {
                $batch = [
                    'Entries' => array_map(
                        [$this, 'formatSendEntry'],
                        $entries
                    ),
                    'QueueUrl' => $queueUrl,
                ];
                $params[] = $batch;
                $result = $this->sqsClient->sendMessageBatch($batch);
                $count = count($result->get('Successful') ?? []);
                $total = count($entries);
                $this->logger->debug("$count/$total successfully sent messages");
            }
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Send messages: {$throwable->getMessage()}",
                $params
            );
        }
    }

    /**
     * @param string $queueUrl
     * @param MessageInterface $message
     */
    public function delete(string $queueUrl, MessageInterface $message): void
    {
        $params = $this->formatDeleteEntry($message, false) +
            ['QueueUrl' => $queueUrl];

        try {
            $result = $this->sqsClient->deleteMessage($params);
            $count = $result->get('Id') ? 1 : 0;
            $this->logger->debug("$count/1 successfully deleted messages");
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Delete message: {$throwable->getMessage()}",
                $params
            );
        }
    }

    /**
     * @param string $queueUrl
     * @param MessageInterface[] $messages
     */
    public function deleteBatch(string $queueUrl, array $messages): void
    {
        $params = [];

        try {
            foreach (array_chunk($messages, self::MAX_MESSAGES) as $entries) {
                $batch = [
                    'Entries' => array_map(
                        [$this, 'formatDeleteEntry'],
                        $entries
                    ),
                    'QueueUrl' => $queueUrl,
                ];
                $params[] = $batch;
                $result = $this->sqsClient->deleteMessageBatch($batch);
                $count = count($result->get('Successful') ?? []);
                $total = count($entries);
                $this->logger->debug(
                    "$count/$total successfully deleted messages"
                );
            }
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Delete messages: {$throwable->getMessage()}",
                $params
            );
        }
    }

    /**
     * @param MessageInterface $message
     * @param bool $isEntities
     * @return array
     */
    private function formatSendEntry(
        MessageInterface $message,
        bool $isEntities = true
    ): array {
        $data = [];

        if ($message->getId() !== null && $isEntities) {
            $data['Id'] = $message->getId();
        }

        if ($message->getDeduplicationId() !== null) {
            $data['MessageDeduplicationId'] = $message->getDeduplicationId();
        }

        $data['MessageBody'] = $message->getBody();

        if ($message->getGroupId() !== null) {
            $data['MessageGroupId'] = $message->getGroupId();
        }

        return $data;
    }

    /**
     * @param MessageInterface $message
     * @param bool $isEntities
     * @return array
     */
    private function formatDeleteEntry(
        MessageInterface $message,
        bool $isEntities = true
    ): array {
        $data = [];

        if ($message->getId() !== null && $isEntities) {
            $data['Id'] = $message->getId();
        }

        $data['ReceiptHandle'] = $message->getReceiptHandle();

        return $data;
    }
}
