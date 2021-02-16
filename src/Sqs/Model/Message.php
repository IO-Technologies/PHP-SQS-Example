<?php

namespace Sqs\Model;

use Sqs\Model\Contract\MessageInterface;

/**
 * Class Message
 * @package Sqs\Model
 */
class Message implements MessageInterface
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $groupId;

    /**
     * @var string|null
     */
    private $deduplicationId;

    /**
     * @var string|null
     */
    private $receiptHandle;

    /**
     * Message constructor.
     * @param string $body
     * @param string|null $id
     * @param string|null $groupId
     * @param string|null $deduplicationId
     * @param string|null $receiptHandle
     */
    public function __construct(
        string $body = '',
        ?string $id = null,
        ?string $groupId = null,
        ?string $deduplicationId = null,
        ?string $receiptHandle = null
    ) {
        $this->setBody($body);
        $this->setId($id);
        $this->setGroupId($groupId);
        $this->setDeduplicationId($deduplicationId);
        $this->setReceiptHandle($receiptHandle);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    /**
     * @param string|null $groupId
     */
    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string|null
     */
    public function getDeduplicationId(): ?string
    {
        return $this->deduplicationId;
    }

    /**
     * @param string|null $deduplicationId
     */
    public function setDeduplicationId(?string $deduplicationId): void
    {
        $this->deduplicationId = $deduplicationId;
    }

    /**
     * @return string|null
     */
    public function getReceiptHandle(): ?string
    {
        return $this->receiptHandle;
    }

    /**
     * @param string|null $receiptHandle
     */
    public function setReceiptHandle(?string $receiptHandle): void
    {
        $this->receiptHandle = $receiptHandle;
    }
}
