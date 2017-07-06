<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueListCommand;
use TriTran\SqsQueueBundle\Service\QueueManager;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueCreateCommandTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Command
 */
class QueueListCommandTest extends KernelTestCase
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
                ->method('listQueue')
                ->willReturnCallback(function ($arg) {
                    return $arg == 'invalid' ? [] : ['queue-url-1', 'queue-url-2'];
                });

            $this->getContainer()->set('tritran.sqs_queue.queue_manager', $this->queueManager);
        }
    }

    /**
     * Test: Returns a list of your queues.
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueListCommand());
        $commandTester->execute([
            '--prefix' => 'queue-prefix'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('queue-url-1', $output);
        $this->assertContains('queue-url-2', $output);
        $this->assertContains('Done', $output);
    }

    /**
     * Test: There are not any queue
     */
    public function testExecuteWithEmpty()
    {
        $commandTester = $this->createCommandTester(new QueueListCommand());
        $commandTester->execute([
            '--prefix' => 'invalid'
        ]);
        $output = $commandTester->getDisplay();
        $this->assertContains(
            'You don\'t have any queue at this moment. Please go to AWS Console to create a new one.',
            $output
        );
    }
}
