<?php

namespace TriTran\SqsQueueBundle\Service;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use TriTran\SqsQueueBundle\Service\Worker\AbstractWorker;

/**
 * Class BaseQueue
 * @package TriTran\SqsQueueBundle\Service
 */
class BaseQueue
{
    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var AbstractWorker
     */
    private $queueWorker;

    /**
     * @var array
     */
    private $attributes;

    /**
     * BaseQueue constructor.
     *
     * @param SqsClient $client
     * @param string $queueName
     * @param string $queueUrl
     * @param AbstractWorker $queueWorker
     * @param array $attributes
     */
    public function __construct(
        SqsClient $client,
        string $queueName,
        string $queueUrl,
        AbstractWorker $queueWorker,
        array $attributes = []
    ) {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
        $this->queueName = $queueName;
        $this->queueWorker = $queueWorker;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function ping()
    {
        $message = (new Message())->setBody('ping');

        return $this->sendMessage($message);
    }

    /**
     * @param Message $message
     * @param int $delay
     *
     * @return string
     */
    public function sendMessage(Message $message, int $delay = 0)
    {
        $params = [
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $message->getBody(),
            'MessageAttributes' => $message->getAttributes()
        ];

        if ($this->isFIFO()) {
            if ($delay) {
                trigger_error('FIFO queues don\'t support per-message delays, only per-queue delays.', E_USER_WARNING);
                $delay = 0;
            }

            if (empty($message->getGroupId())) {
                throw new \InvalidArgumentException('MessageGroupId is required for FIFO queues.');
            }
            $params['MessageGroupId'] = $message->getGroupId();

            if (!empty($message->getDeduplicationId())) {
                $params['MessageDeduplicationId'] = $message->getDeduplicationId();
            }
        }

        if ($delay) {
            $params['DelaySeconds'] = $delay;
        }

        try {
            $result = $this->client->sendMessage($params);
            $messageId = $result->get('MessageId');
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $messageId;
    }

    /**
     * Retrieves one or more messages (up to 10), from the specified queue.
     *
     * @param int $limit
     *
     * @return MessageCollection|Message[]
     */
    public function receiveMessage(int $limit = 1)
    {
        $collection = new MessageCollection([]);

        try {
            $result = $this->client->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'AttributeNames' => ['All'],
                'MessageAttributeNames' => ['All'],
                'MaxNumberOfMessages' => $limit,
                'VisibilityTimeout' => $this->attributes['VisibilityTimeout'] ?? 30,
                'WaitTimeSeconds' => $this->attributes['ReceiveMessageWaitTimeSeconds'] ?? 0,
            ]);

            $messages = $result->get('Messages') ?? [];
            foreach ($messages as $message) {
                $collection->append(
                    (new Message())
                        ->setId($message['MessageId'])
                        ->setBody($message['Body'])
                        ->setReceiptHandle($message['ReceiptHandle'])
                        ->setAttributes($message['Attributes'])
                        ->setMessageAttributes($message['MessageAttributes'] ?? [])
                );
            }
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $collection;
    }

    /**
     * Deletes the specified message from the specified queue
     *
     * @param Message $message
     *
     * @return bool
     */
    public function deleteMessage(Message $message)
    {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle()
            ]);

            return true;
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }
    }

    /**
     * Releases a message back to the queue, making it visible again
     *
     * @param Message $message
     *
     * @return bool
     */
    public function releaseMessage(Message $message)
    {
        try {
            $this->client->changeMessageVisibility([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle(),
                'VisibilityTimeout' => 0
            ]);

            return true;
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }
    }

    /**
     * Deletes the messages in a queue.
     * When you use the this action, you can't retrieve a message deleted from a queue.
     *
     * @return bool
     */
    public function purge()
    {
        try {
            $this->client->purgeQueue([
                'QueueUrl' => $this->queueUrl
            ]);

            return true;
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }
    }

    /**
     * @return string
     */
    public function getQueueUrl(): string
    {
        return $this->queueUrl;
    }

    /**
     * @param string $queueUrl
     *
     * @return $this
     */
    public function setQueueUrl(string $queueUrl)
    {
        $this->queueUrl = $queueUrl;

        return $this;
    }

    /**
     * @return AbstractWorker
     */
    public function getQueueWorker(): AbstractWorker
    {
        return $this->queueWorker;
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return SqsClient
     */
    public function getClient(): SqsClient
    {
        return $this->client;
    }

    /**
     * @return bool
     */
    final public function isFIFO(): bool
    {
        return QueueManager::isFifoQueue($this->getQueueName());
    }
}
