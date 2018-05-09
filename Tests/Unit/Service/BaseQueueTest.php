<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\Service;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sqs\SqsClient;
use PHPUnit\Framework\TestCase;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Service\Message;
use TriTran\SqsQueueBundle\Service\MessageCollection;
use TriTran\SqsQueueBundle\Tests\app\Worker\BasicWorker;

/**
 * Class BaseQueueTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Service
 */
class BaseQueueTest extends TestCase
{
    /**
     * @param array $entries
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Result
     */
    private function getAwsResult($entries)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Result $result */
        $result = $this->getMockBuilder(Result::class)->getMock();
        $result->expects($this->any())
            ->method('get')
            ->withAnyParameters()
            ->willReturnCallback(function ($arg) use ($entries) {
                return $entries[$arg] ?? [];
            });

        return $result;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SqsClient
     */
    private function getAwsClient()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|SqsClient $client */
        $client = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendMessage', 'receiveMessage', 'deleteMessage', 'changeMessageVisibility', 'purgeQueue'])
            ->getMock();

        return $client;
    }

    /**
     * @return BaseQueue
     */
    public function getQueue()
    {
        $client = $this->getAwsClient();
        $queueName = 'queue-name';
        $queueUrl = 'queue-url';
        $queueWorker = new BasicWorker();
        $queueAttr = ['a', 'b', 'c', 'd'];

        return new BaseQueue($client, $queueName, $queueUrl, $queueWorker, $queueAttr);
    }

    /**
     * Test: Construction
     */
    public function testConstruction()
    {
        $client = $this->getAwsClient();
        $queueName = 'queue-name';
        $queueUrl = 'queue-url';
        $queueWorker = new BasicWorker();
        $queueAttr = ['a', 'b', 'c', 'd'];
        $queue = new BaseQueue($client, $queueName, $queueUrl, $queueWorker, $queueAttr);

        $this->assertInstanceOf(BaseQueue::class, $queue);
        $this->assertEquals($client, $queue->getClient());
        $this->assertEquals($queueName, $queue->getQueueName());
        $this->assertEquals($queueUrl, $queue->getQueueUrl());
        $this->assertEquals($queueWorker, $queue->getQueueWorker());
        $this->assertEquals($queueAttr, $queue->getAttributes());
    }

    /**
     * Test: Getter/Setter
     */
    public function testGetterSetter()
    {
        $client = $this->getAwsClient();
        $queueUrl = 'queue-url';
        $queueAttr = ['a', 'b', 'c', 'd'];

        $queue = new BaseQueue($client, '', '', new BasicWorker(), []);

        $this->assertInstanceOf(BaseQueue::class, $queue->setQueueUrl($queueUrl));
        $this->assertEquals($queueUrl, $queue->getQueueUrl());
        $this->assertInstanceOf(BaseQueue::class, $queue->setAttributes($queueAttr));
        $this->assertEquals($queueAttr, $queue->getAttributes());
    }

    /**
     * Test: send message to a queue
     */
    public function testSendMessage()
    {
        $delay = random_int(1, 10);
        $messageBody = 'my-message';
        $messageAttr = ['x', 'y', 'z'];
        $queueUrl = 'queue-url';

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('sendMessage')
            ->with([
                'DelaySeconds' => $delay,
                'MessageAttributes' => $messageAttr,
                'MessageBody' => $messageBody,
                'QueueUrl' => $queueUrl
            ])
            ->willReturn($this->getAwsResult(['MessageId' => 'new-message-id']));

        $queue = new BaseQueue($client, 'queue-name', $queueUrl, new BasicWorker(), []);
        $this->assertEquals(
            'new-message-id',
            $queue->sendMessage(new Message($messageBody, $messageAttr), $delay)
        );
    }

    /**
     * Test: send message to a queue in failure
     */
    public function testSendMessageFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('sendMessage')
            ->withAnyParameters()
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('send-message-command')
            ));

        $queue = new BaseQueue($client, 'bad-queue-name', 'bad-queue-url', new BasicWorker(), []);

        $this->expectException(\InvalidArgumentException::class);
        $queue->sendMessage(new Message('my-message', []));
    }

    /**
     * Test: receive Message
     */
    public function testReceiveMessage()
    {
        $limit = random_int(1, 10);
        $queueUrl = 'queue-url';
        $queueAttr = [
            'ReceiveMessageWaitTimeSeconds' => random_int(1, 10),
            'VisibilityTimeout' => random_int(0, 30)
        ];
        $expected = [
            [
                'MessageId' => 'my-message-id',
                'Body' => 'my-body',
                'ReceiptHandle' => 'receipt-handle',
                'Attributes' => [],
            ]
        ];

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('receiveMessage')
            ->with([
                'AttributeNames' => ['All'],
                'MaxNumberOfMessages' => $limit,
                'MessageAttributeNames' => ['All'],
                'QueueUrl' => $queueUrl,
                'WaitTimeSeconds' => $queueAttr['ReceiveMessageWaitTimeSeconds'],
                'VisibilityTimeout' => $queueAttr['VisibilityTimeout'],
            ])
            ->willReturn($this->getAwsResult(['Messages' => $expected]));

        $queue = new BaseQueue($client, 'queue-name', $queueUrl, new BasicWorker(), $queueAttr);
        $result = $queue->receiveMessage($limit);
        $this->assertInstanceOf(MessageCollection::class, $result);
        $this->assertEquals($this->arrayMessageToCollection($expected), $result);
    }

    /**
     * @param array $messages
     *
     * @return MessageCollection
     */
    private function arrayMessageToCollection($messages)
    {
        $collection = new MessageCollection([]);
        foreach ($messages as $message) {
            $collection->append(
                (new Message())
                    ->setId($message['MessageId'])
                    ->setBody($message['Body'])
                    ->setReceiptHandle($message['ReceiptHandle'])
                    ->setAttributes($message['Attributes'])
            );
        }

        return $collection;
    }

    /**
     * Test: receive Message in failure
     */
    public function testReceiveMessageFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('receiveMessage')
            ->withAnyParameters()
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('receive-message-command')
            ));

        $queue = new BaseQueue($client, 'bad-queue-name', 'bad-queue-url', new BasicWorker(), []);
        $this->expectException(\InvalidArgumentException::class);
        $queue->receiveMessage();
    }

    /**
     * Test: Delete a message from queue
     */
    public function testDeleteMessage()
    {
        $queueUrl = 'queue-url';
        $message = (new Message())->setReceiptHandle('my-receipt-handle');

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('deleteMessage')
            ->with([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle()
            ])
            ->willReturn(true);

        $queue = new BaseQueue($client, 'queue-name', $queueUrl, new BasicWorker(), []);
        $this->assertTrue($queue->deleteMessage($message));
    }

    /**
     * Test: Delete a message from queue in failure
     */
    public function testDeleteMessageFailure()
    {
        $queueUrl = 'bad-queue-url';
        $message = (new Message())->setReceiptHandle('my-receipt-handle');

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('deleteMessage')
            ->with([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle()
            ])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('delete-message-command')
            ));

        $queue = new BaseQueue($client, 'bad-queue-name', $queueUrl, new BasicWorker(), []);
        $this->expectException(\InvalidArgumentException::class);
        $queue->deleteMessage($message);
    }

    /**
     * Test: Release a message from processing, making it visible again
     */
    public function testReleaseMessage()
    {
        $queueUrl = 'queue-url';
        $message = (new Message())->setReceiptHandle('my-receipt-handle');

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('changeMessageVisibility')
            ->with([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle(),
                'VisibilityTimeout' => 0
            ])
            ->willReturn(true);

        $queue = new BaseQueue($client, 'queue-name', $queueUrl, new BasicWorker(), []);
        $this->assertTrue($queue->releaseMessage($message));
    }

    /**
     * Test: Release a message from processing in failure
     */
    public function testReleaseMessageFailure()
    {
        $queueUrl = 'bad-queue-url';
        $message = (new Message())->setReceiptHandle('my-receipt-handle');

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('changeMessageVisibility')
            ->with([
                'QueueUrl' => $queueUrl,
                'ReceiptHandle' => $message->getReceiptHandle(),
                'VisibilityTimeout' => 0
            ])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('release-message-command')
            ));

        $queue = new BaseQueue($client, 'bad-queue-name', $queueUrl, new BasicWorker(), []);
        $this->expectException(\InvalidArgumentException::class);
        $queue->releaseMessage($message);
    }

    /**
     * Test: Delete a message from queue
     */
    public function testPurge()
    {
        $queueUrl = 'queue-url';

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('purgeQueue')
            ->with([
                'QueueUrl' => $queueUrl
            ])
            ->willReturn(true);

        $queue = new BaseQueue($client, 'queue-name', $queueUrl, new BasicWorker(), []);
        $this->assertTrue($queue->purge());
    }

    /**
     * Test: Delete a message from queue in failure
     */
    public function testPurgeFailure()
    {
        $queueUrl = 'bad-queue-url';

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('purgeQueue')
            ->with([
                'QueueUrl' => $queueUrl
            ])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('delete-message-command')
            ));

        $queue = new BaseQueue($client, 'bad-queue-name', $queueUrl, new BasicWorker(), []);
        $this->expectException(\InvalidArgumentException::class);
        $queue->purge();
    }
}
