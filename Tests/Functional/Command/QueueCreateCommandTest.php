<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueCreateCommand;
use TriTran\SqsQueueBundle\Service\QueueManager;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueCreateCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Command
 */
class QueueCreateCommandTest extends KernelTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|QueueManager
     */
    private $queueManager;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        if ($this->queueManager === null) {
            $this->queueManager = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->queueManager
                ->expects($this->any())
                ->method('createQueue')
                ->with('my-queue-name', [
                    'DelaySeconds' => 1,
                    'MaximumMessageSize' => 1,
                    'MessageRetentionPeriod' => 1,
                    'ReceiveMessageWaitTimeSeconds' => 1,
                    'VisibilityTimeout' => 1,
                    'ContentBasedDeduplication' => true,
                ])
                ->willReturn('new-queue-url');

            $this->getContainer()->set('tritran.sqs_queue.queue_manager', $this->queueManager);
        }
    }

    /**
     * Test: Create a queue by name and basic attributions
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueCreateCommand());
        $commandTester->execute([
            'name' => 'my-queue-name',
            '--delay_seconds' => 1,
            '--maximum_message_size' => 1,
            '--message_retention_period' => 1,
            '--receive_message_wait_time_seconds' => 1,
            '--visibility_timeout' => 1,
            '--content_based_deduplication' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Created successfully. New Queue URL: new-queue-url', $output);
    }

    /**
     * Test: Create a new queue which was configured or existed already.
     */
    public function testExecuteWithAnExistingQueueName()
    {
        $commandTester = $this->createCommandTester(new QueueCreateCommand());

        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute([
            'name' => 'basic_queue' // Existed
        ]);
    }
}
