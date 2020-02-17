<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueAttrCommand;
use TriTran\SqsQueueBundle\Service\QueueManager;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueAttrCommandTest
 */
class QueueAttrCommandTest extends KernelTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|QueueManager
     */
    private $queueManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->queueManager = $this->getMockBuilder(QueueManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queueManager
            ->expects($this->any())
            ->method('getQueueAttributes')
            ->with('my-queue-url')
            ->willReturn(['att1' => 'value1', 'att2' => 'value2']);

        $this->getContainer()->set('tritran.sqs_queue.queue_manager', $this->queueManager);
    }

    /**
     * Test: Retrieve the attribute of a specified queue.
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueAttrCommand());
        $commandTester->execute([
            'url' => 'my-queue-url',
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains('value1', $output);
        $this->assertContains('value2', $output);
        $this->assertContains('Done', $output);
    }
}
