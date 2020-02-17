<?php

namespace TriTran\SqsQueueBundle\Tests\Functional\Command;

use TriTran\SqsQueueBundle\Command\QueueWorkerCommand;
use TriTran\SqsQueueBundle\Service\BaseWorker;
use TriTran\SqsQueueBundle\Tests\app\KernelTestCase;

/**
 * Class QueueWorkerCommandTest.
 */
class QueueWorkerCommandTest extends KernelTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|BaseWorker
     */
    private $baseWorker;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        if ($this->baseWorker === null) {
            $this->baseWorker = $this->getMockBuilder(BaseWorker::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->baseWorker
                ->expects($this->any())
                ->method('start')
                ->willReturn(true);

            $this->getContainer()->set('tritran.sqs_queue.queue_worker', $this->baseWorker);
        }
    }

    /**
     * Test: start a worker for a non-existing queue.
     */
    public function testExecuteWithNonExistingQueue()
    {
        $commandTester = $this->createCommandTester(new QueueWorkerCommand());

        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute([
            'name' => 'non-existing-queue',
        ]);
    }

    /**
     * Test: start a worker with an invalid value of amount of messages.
     */
    public function testExecuteWithInvalidAmountMessages()
    {
        $commandTester = $this->createCommandTester(new QueueWorkerCommand());

        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute([
            'name'       => 'basic_queue',
            '--messages' => -1,
        ]);
    }

    /**
     * Test: Start a worker for listening to a queue.
     */
    public function testExecute()
    {
        $commandTester = $this->createCommandTester(new QueueWorkerCommand());
        $commandTester->execute([
            'name' => 'basic_queue',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Start listening to queue', $output);
    }

    /**
     * Test: invalid input limit should throw an exception.
     */
    public function testInvalidInputLimit()
    {
        $commandTester = $this->createCommandTester(new QueueWorkerCommand());

        $this->expectException(\InvalidArgumentException::class);
        $commandTester->execute([
            'name'    => 'basic_queue',
            '--limit' => 0,
        ]);
    }
}
