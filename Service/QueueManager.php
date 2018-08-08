<?php

namespace TriTran\SqsQueueBundle\Service;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;

/**
 * Class QueueManager
 * @package TriTran\SqsQueueBundle\Service
 */
class QueueManager
{
    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var array
     */
    const QUEUE_ATTR_DEFAULT = [
        'DelaySeconds' => 0,
        'MaximumMessageSize' => 262144, // 256 KiB
        'MessageRetentionPeriod' => 345600, // 4 days
        'ReceiveMessageWaitTimeSeconds' => 0,
        'VisibilityTimeout' => 30,
        'RedrivePolicy' => '',
    ];

    /**
     * QueueManager constructor.
     *
     * @param SqsClient $client
     */
    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $prefix
     *
     * @return array
     */
    public function listQueue(string $prefix = '')
    {
        $queues = [];
        $attr = [];
        if (!empty($prefix)) {
            $attr['QueueNamePrefix'] = $prefix;
        }

        try {
            $result = $this->client->listQueues($attr);
            if ($result->count()) {
                $queues = $result->get('QueueUrls');
            }
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $queues;
    }

    /**
     * @param string $queueName
     * @param array $queueAttribute
     *
     * @return string
     */
    public function createQueue(string $queueName, array $queueAttribute = [])
    {
        $queryUrl = '';

        $queueAttribute = array_filter($queueAttribute, function ($key) {
            return array_key_exists($key, self::QUEUE_ATTR_DEFAULT);
        }, ARRAY_FILTER_USE_KEY);
        $attr = [
            'Attributes' => array_merge(self::QUEUE_ATTR_DEFAULT, $queueAttribute),
            'QueueName' => $queueName
        ];

        try {
            $result = $this->client->createQueue($attr);
            if ($result->count()) {
                $queryUrl = $result->get('QueueUrl');
            }
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $queryUrl;
    }

    /**
     * @param string $queueUrl
     *
     * @return bool
     */
    public function deleteQueue(string $queueUrl)
    {
        try {
            $this->client->deleteQueue(['QueueUrl' => $queueUrl]);

            return true;
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }
    }

    /**
     * @param string $queueUrl
     * @param array $queueAttribute
     *
     * @return bool
     */
    public function setQueueAttributes(string $queueUrl, array $queueAttribute)
    {
        $queueAttribute = array_filter($queueAttribute, function ($key) {
            return array_key_exists($key, self::QUEUE_ATTR_DEFAULT);
        }, ARRAY_FILTER_USE_KEY);
        $attr = [
            'Attributes' => $queueAttribute,
            'QueueUrl' => $queueUrl
        ];

        try {
            $this->client->setQueueAttributes($attr);

            return true;
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }
    }

    /**
     * @param string $queueUrl
     *
     * @return array
     */
    public function getQueueAttributes(string $queueUrl)
    {
        $attr = [];

        try {
            $result = $this->client->getQueueAttributes([
                'AttributeNames' => ['All'],
                'QueueUrl' => $queueUrl
            ]);
            if ($result->count()) {
                $attr = $result->get('Attributes');
            }
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $attr;
    }

    /**
     * @return SqsClient
     */
    public function getClient(): SqsClient
    {
        return $this->client;
    }
}
