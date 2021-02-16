<?php

namespace Sqs\Model\Contract;

/**
 * Interface MessageInterface
 * @package Sqs\Model\Contract
 */
interface MessageInterface
{
    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @return string|null
     */
    public function getGroupId(): ?string;

    /**
     * @return string|null
     */
    public function getDeduplicationId(): ?string;

    /**
     * @return string|null
     */
    public function getReceiptHandle(): ?string;
}
