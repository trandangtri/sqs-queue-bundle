<?php

namespace TriTran\SqsQueueBundle\Service;

use Aws\Sqs\SqsClient;
use TriTran\SqsQueueBundle\Service\Worker\AbstractWorker;

/**
 * Class QueueFactory
 * @package TriTran\SqsQueueBundle\Service
 */
class QueueFactory
{
    /**
     * @var BaseQueue[]
     */
    private $queues;

    /**
     * @var SqsClient
     */
    private $client;

    /**
     * QueueFactory constructor.
     *
     * @param SqsClient $client
     */
    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param SqsClient $client
     * @param string $queueName
     * @param string $queueUrl
     * @param AbstractWorker $queueWorker
     * @param array $options
     *
     * @return BaseQueue
     */
    public static function createQueue(
        SqsClient $client,
        string $queueName,
        string $queueUrl,
        AbstractWorker $queueWorker,
        array $options = []
    ) {
        $instance = new self($client);

        return $instance->create($queueName, $queueUrl, $queueWorker, $options);
    }

    /**
     * @param string $queueName
     * @param string $queueUrl
     * @param AbstractWorker $queueWorker
     * @param array $options
     *
     * @return BaseQueue
     */
    public function create(
        string $queueName,
        string $queueUrl,
        AbstractWorker $queueWorker,
        array $options = []
    ) {
        if ($this->queues === null) {
            $this->queues = [];
        }

        if (isset($this->queues[$queueUrl])) {
            return $this->queues[$queueUrl];
        }

        $queue = new BaseQueue($this->client, $queueName, $queueUrl, $queueWorker, $options);
        $this->queues[$queueUrl] = $queue;

        return $queue;
    }

    /**
     * @return SqsClient
     */
    public function getClient(): SqsClient
    {
        return $this->client;
    }
}
