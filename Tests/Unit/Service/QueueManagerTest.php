<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\Service;

use Aws\Command;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Sqs\SqsClient;
use PHPUnit\Framework\TestCase;
use TriTran\SqsQueueBundle\Service\QueueManager;

/**
 * Class QueueManagerTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Service
 */
class QueueManagerTest extends TestCase
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
            ->method('count')
            ->willReturn(count($entries));
        $result->expects($this->any())
            ->method('get')
            ->withAnyParameters()
            ->willReturnCallback(function ($arg) use ($entries) {
                return isset($entries[$arg]) ? $entries[$arg] : [];
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
            ->setMethods(['listQueues', 'createQueue', 'deleteQueue', 'setQueueAttributes', 'getQueueAttributes'])
            ->getMock();

        return $client;
    }

    /**
     * Test Construction
     */
    public function testConstruction()
    {
        $client = $this->getAwsClient();
        $manager = new QueueManager($client);

        $this->assertInstanceOf(QueueManager::class, $manager);
        $this->assertEquals($client, $manager->getClient());
    }

    /**
     * Test: get the list of queue
     */
    public function testListQueue()
    {
        $expected = ['queue-url-1', 'queue-url-2'];

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('listQueues')
            ->with(['QueueNamePrefix' => 'queue-prefix'])
            ->willReturn($this->getAwsResult(['QueueUrls' => $expected]));

        $manager = new QueueManager($client);
        $this->assertEquals($expected, $manager->listQueue('queue-prefix'));
    }

    /**
     * Test: get the list of queue in failure
     */
    public function testListQueueFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('listQueues')
            ->with(['QueueNamePrefix' => 'bad-queue-prefix'])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('list-queue-command')
            ));

        $manager = new QueueManager($client);
        $this->expectException(\InvalidArgumentException::class);
        $manager->listQueue('bad-queue-prefix');
    }

    /**
     * Test: try to create a new queue
     */
    public function testCreateQueue()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('createQueue')
            ->with([
                'Attributes' => QueueManager::QUEUE_ATTR_DEFAULT,
                'QueueName' => 'queue-url'
            ])
            ->willReturn($this->getAwsResult(['QueueUrl' => 'queue-url']));

        $manager = new QueueManager($client);
        $this->assertEquals('queue-url', $manager->createQueue('queue-url', QueueManager::QUEUE_ATTR_DEFAULT));
    }

    /**
     * Test: try to create a new queue in failure
     */
    public function testCreateFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('createQueue')
            ->with([
                'Attributes' => QueueManager::QUEUE_ATTR_DEFAULT,
                'QueueName' => 'bad-queue-url'
            ])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('create-queue-command')
            ));

        $manager = new QueueManager($client);
        $this->expectException(\InvalidArgumentException::class);
        $manager->createQueue('bad-queue-url', QueueManager::QUEUE_ATTR_DEFAULT);
    }

    /**
     * Test: try to delete a new queue
     */
    public function testDeleteQueue()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('deleteQueue')
            ->with(['QueueUrl' => 'queue-url'])
            ->willReturn($this->getAwsResult([]));

        $manager = new QueueManager($client);
        $this->assertTrue($manager->deleteQueue('queue-url'));
    }

    /**
     * Test: try to delete a new queue in failure
     */
    public function testDeleteFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('deleteQueue')
            ->with(['QueueUrl' => 'bad-queue-url'])
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('delete-queue-command')
            ));

        $manager = new QueueManager($client);
        $this->expectException(\InvalidArgumentException::class);
        $manager->deleteQueue('bad-queue-url');
    }

    /**
     * Test: Queue Attributes Setter/Getter
     */
    public function testQueueAttributesSetterGetter()
    {
        $attributes = [
            'DelaySeconds' => rand(0, 100),
            'MaximumMessageSize' => rand(0, 100),
            'MessageRetentionPeriod' => rand(0, 100),
            'ReceiveMessageWaitTimeSeconds' => rand(0, 100),
            'VisibilityTimeout' => rand(0, 100)
        ];

        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('setQueueAttributes')
            ->with([
                'Attributes' => $attributes,
                'QueueUrl' => 'queue-url'
            ])
            ->willReturn($this->getAwsResult([]));

        $client->expects($this->any())
            ->method('getQueueAttributes')
            ->with([
                'AttributeNames' => ['All'],
                'QueueUrl' => 'queue-url'
            ])
            ->willReturn($this->getAwsResult(['Attributes' => $attributes]));

        $manager = new QueueManager($client);
        $this->assertTrue($manager->setQueueAttributes('queue-url', $attributes));
        $this->assertEquals($attributes, $manager->getQueueAttributes('queue-url'));
    }

    /**
     * Test: Queue Attributes Setter in failure
     */
    public function testQueueAttributesSetterFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('setQueueAttributes')
            ->withAnyParameters()
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('delete-queue-command')
            ));

        $manager = new QueueManager($client);
        $this->expectException(\InvalidArgumentException::class);
        $manager->setQueueAttributes('bad-queue-url', []);
    }

    /**
     * Test: Queue Attributes Getter in failure
     */
    public function testQueueAttributesGetterFailure()
    {
        $client = $this->getAwsClient();
        $client->expects($this->any())
            ->method('getQueueAttributes')
            ->withAnyParameters()
            ->willThrowException(new AwsException(
                'AWS Client Exception',
                new Command('delete-queue-command')
            ));

        $manager = new QueueManager($client);
        $this->expectException(\InvalidArgumentException::class);
        $manager->getQueueAttributes('bad-queue-url');
    }
}
