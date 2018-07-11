<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueuePurgeCommand;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueDeleteCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Functional\Command
 */
class QueuePurgeCommandTest extends KernelTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|BaseQueue
     */
    private $queue;
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        if ($this->queue === null) {
            $this->queue = $this->getMockBuilder(BaseQueue::class)->disableOriginalConstructor()->getMock();
            $this->queue->expects($this->any())->method('purge')->willReturn(true);
            $this->getContainer()->set('tritran.sqs_queue.basic_queue', $this->queue);
        }
    }
    /**
     * Test: Purge a queue without force option
     */
    public function testExecuteWithoutForce()
    {
        $commandTester = $this->createCommandTester(new QueuePurgeCommand());
        $commandTester->execute(['name' => 'my-queue-name']);
        $output = $commandTester->getDisplay();
        $this->assertContains('Option --force is mandatory to drop data', $output);
    }
    /**
     * Test: Purge a queue with a non-existing queue
     */
    public function testExecuteWithNonExistingQueue()
    {
        $commandTester = $this->createCommandTester(new QueuePurgeCommand());
        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute(['name' => 'non-existing-queue', '--force' => true]);
    }
    /**
     * Test: Delete a queue without force option
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueuePurgeCommand());
        $commandTester->execute(['name' => 'basic_queue', '--force' => true]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Done', $output);
    }
}