<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueUpdateCommand;
use TriTran\SqsQueueBundle\Service\QueueManager;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueUpdateCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Functional\Command
 */
class QueueUpdateCommandTest extends KernelTestCase
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
            $this->queueManager->expects($this->any())->method('listQueue')->willReturn(['aws-basic-queue-url']);
            $this->queueManager->expects($this->any())->method('setQueueAttributes')->willReturn(true);
            $this->getContainer()->set('tritran.sqs_queue.queue_manager', $this->queueManager);
        }
    }
    /**
     * Test: Update Queue attribute based on configuration without force option
     */
    public function testExecuteWithoutForce()
    {
        $commandTester = $this->createCommandTester(new QueueUpdateCommand());
        $commandTester->execute([]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Option --force is mandatory to update data', $output);
    }
    /**
     * Test: Delete a queue without force option
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueUpdateCommand());
        $commandTester->execute(['--force' => true]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Done', $output);
    }
}