<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueuePingCommand;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueuePingCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Functional\Command
 */
class QueuePingCommandTest extends KernelTestCase
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
            $this->queue = $this->getMockBuilder(BaseQueue::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->queue
                ->expects($this->any())
                ->method('ping')
                ->willReturn('new-message-id');

            $this->getContainer()->set('tritran.sqs_queue.basic_queue', $this->queue);
        }
    }

    /**
     * Test: Purge a queue with a non-existing queue
     */
    public function testExecuteWithNonExistingQueue()
    {
        $commandTester = $this->createCommandTester(new QueuePingCommand());

        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute([
            'name' => 'non-existing-queue'
        ]);
    }

    /**
     * Test: Delete a queue without force option
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueuePingCommand());
        $commandTester->execute([
            'name' => 'basic_queue'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Done', $output);
    }
}
