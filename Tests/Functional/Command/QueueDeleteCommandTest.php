<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueDeleteCommand;
use TriTran\SqsQueueBundle\Service\QueueManager;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueDeleteCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Functional\Command
 */
class QueueDeleteCommandTest extends KernelTestCase
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
            $this->queueManager = $this->getMockBuilder(QueueManager::class)->disableOriginalConstructor()->getMock();
            $this->queueManager->expects($this->any())->method('deleteQueue')->with('my-queue-url')->willReturn(true);
            $this->getContainer()->set('tritran.sqs_queue.queue_manager', $this->queueManager);
        }
    }
    /**
     * Test: Delete a queue without force option
     */
    public function testExecuteWithoutForce()
    {
        $commandTester = $this->createCommandTester(new QueueDeleteCommand());
        $commandTester->execute(['url' => 'my-queue-url']);
        $output = $commandTester->getDisplay();
        $this->assertContains('Option --force is mandatory to drop data', $output);
    }
    /**
     * Test: Delete a queue without force option
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueDeleteCommand());
        $commandTester->execute(['url' => 'my-queue-url', '--force' => true]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Done', $output);
    }
}