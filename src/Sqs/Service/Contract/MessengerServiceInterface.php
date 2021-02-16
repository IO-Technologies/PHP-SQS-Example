<?php

namespace Sqs\Service\Contract;

use Sqs\Model\Contract\MessageInterface;

/**
 * Interface MessengerServiceInterface
 * @package Sqs\Service\Contract
 */
interface MessengerServiceInterface
{
    /**
     * @param string $queueUrl
     * @param int $maxMessages
     * @return MessageInterface[]
     */
    public function receive(string $queueUrl, int $maxMessages = 1): array;

    /**
     * @param string $queueUrl
     * @param MessageInterface $message
     */
    public function send(string $queueUrl, MessageInterface $message): void;

    /**
     * @param string $queueUrl
     * @param MessageInterface[] $messages
     */
    public function sendBatch(string $queueUrl, array $messages): void;

    /**
     * @param string $queueUrl
     * @param MessageInterface $message
     */
    public function delete(string $queueUrl, MessageInterface $message): void;

    /**
     * @param string $queueUrl
     * @param MessageInterface[] $messages
     */
    public function deleteBatch(string $queueUrl, array $messages): void;
}
