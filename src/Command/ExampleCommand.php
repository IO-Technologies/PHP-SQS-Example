<?php

namespace Command;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Sqs\Model\Message;
use Sqs\Service\Contract\QueueServiceInterface;
use Sqs\Service\Contract\MessengerServiceInterface;
use Throwable;

/**
 * Class ExampleCommand
 * @package Command
 */
class ExampleCommand
{
    private const FIRST_MASSAGE_BODY = 'First test message';
    private const SECOND_MASSAGE_BODY = 'Second test message';
    private const THIRD_MASSAGE_BODY = 'Third test message';

    /**
     * @var QueueServiceInterface
     */
    private $queueService;

    /**
     * @var MessengerServiceInterface
     */
    private $messengerService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ExampleCommand constructor.
     * @param QueueServiceInterface $queueService
     * @param MessengerServiceInterface $messengerService
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        QueueServiceInterface $queueService,
        MessengerServiceInterface $messengerService,
        ?LoggerInterface $logger = null
    ) {
        $this->queueService = $queueService;
        $this->messengerService = $messengerService;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string $queueName
     * @return int
     */
    public function execute(string $queueName = 'your_test_queue.fifo'): int
    {
        try {
            $queueUrl = $this->queueService->getUrl($queueName);
            if ($queueUrl === null) {
                $queueUrl = $this->queueService->create($queueName);
            }

            if ($queueUrl === null) {
                throw new RuntimeException('Undefined queue URL');
            }

            $this->messengerService->send(
                $queueUrl,
                new Message(self::FIRST_MASSAGE_BODY)
            );

            $this->messengerService->sendBatch(
                $queueUrl,
                [
                    new Message(self::SECOND_MASSAGE_BODY),
                    new Message(self::THIRD_MASSAGE_BODY),
                ]
            );

            $firstMessages = $this->messengerService->receive($queueUrl);
            if (empty($firstMessages[0])) {
                throw new RuntimeException('Empty first test message');
            }

            if ($firstMessages[0]->getBody() === self::FIRST_MASSAGE_BODY) {
                throw new RuntimeException('Invalid first test message');
            }

            $this->messengerService->delete($queueUrl, $firstMessages[0]);

            $otherMessages = $this->messengerService->receive($queueUrl, 2);
            if (empty($otherMessages[0])) {
                throw new RuntimeException('Empty second test message');
            }

            if ($otherMessages[0]->getBody() === self::SECOND_MASSAGE_BODY) {
                throw new RuntimeException('Invalid second test message');
            }

            if (empty($otherMessages[1])) {
                throw new RuntimeException('Empty third test message');
            }

            if ($otherMessages[0]->getBody() === self::THIRD_MASSAGE_BODY) {
                throw new RuntimeException('Invalid third test message');
            }

            $this->messengerService->deleteBatch($queueUrl, $otherMessages);

            $this->logger->info('SQS wrapper works as expected');
        } catch (Throwable $throwable) {
            $this->logger->error(
                "Execute `{$queueName}` queue: {$throwable->getMessage()}"
            );

            return 1;
        }

        return 0;
    }
}
