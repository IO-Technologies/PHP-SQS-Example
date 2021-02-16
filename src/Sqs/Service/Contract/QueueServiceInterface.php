<?php

namespace Sqs\Service\Contract;

/**
 * Interface QueueServiceInterface
 * @package Sqs\Service\Contract
 */
interface QueueServiceInterface
{
    /**
     * @param string $name
     * @return string|null
     */
    public function getUrl(string $name): ?string;

    /**
     * @param string $name
     * @return string|null
     */
    public function create(string $name): ?string;
}
