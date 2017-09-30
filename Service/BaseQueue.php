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
     * @param array $options
     */
    public function __construct(
        SqsClient $client,
        string $queueName,
        string $queueUrl,
        AbstractWorker $queueWorker,
        array $options = []
    ) {
        $this->client = $client;
        $this->queueUrl = $queueUrl;
        $this->queueName = $queueName;
        $this->queueWorker = $queueWorker;
        $this->attributes = $options;
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
            'DelaySeconds' => $delay,
            'MessageAttributes' => $message->getAttributes(),
            'MessageBody' => $message->getBody(),
            'QueueUrl' => $this->queueUrl
        ];
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
                'AttributeNames' => ['SentTimestamp'],
                'MaxNumberOfMessages' => $limit,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $this->queueUrl,
                'WaitTimeSeconds' => $this->attributes['receive_message_wait_time_seconds'] ?? 0,
            ]);

            $messages = $result->get('Messages');
            if ($messages !== null) {
                for ($i = 0, $n = count($messages); $i < $n; $i++) {
                    $message = new Message();
                    $message->setId($messages[$i]['MessageId']);
                    $message->setBody($messages[$i]['Body']);
                    $message->setReceiptHandle($messages[$i]['ReceiptHandle']);
                    $message->setAttributes($messages[$i]['Attributes']);

                    $collection->append($message);
                }
            }
        } catch (AwsException $e) {
            throw new \InvalidArgumentException($e->getAwsErrorMessage());
        }

        return $collection;
    }

    /**
     * Deletes the specified message from the specified queue
     *
     * @param string $receiptHandle An identifier associated with the act of receiving the message
     *
     * @return bool
     */
    public function deleteMessage(string $receiptHandle)
    {
        try {
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $receiptHandle
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
}
