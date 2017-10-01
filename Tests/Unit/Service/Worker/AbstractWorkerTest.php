<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\Service\Worker;

use PHPUnit\Framework\TestCase;
use TriTran\SqsQueueBundle\Service\Message;
use TriTran\SqsQueueBundle\Service\Worker\AbstractWorker;

/**
 * Class AbstractWorkerTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Service\Worker
 */
class AbstractWorkerTest extends TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractWorker
     */
    private function getAbstractWorker()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractWorker $client */
        $worker = $this->getMockBuilder(AbstractWorker::class)
            ->getMockForAbstractClass();

        return $worker;
    }

    /**
     * Test: Main processing of each worker with a ping pong message
     */
    public function testProcessWithPingPong()
    {
        $worker = $this->getAbstractWorker();

        $message = (new Message())->setBody('ping');
        $expect = 'Pong. Now is ' . (new \DateTime('now'))->format('M d, Y H:i:s') . PHP_EOL;

        $this->expectOutputString($expect);
        $result = $worker->process($message);
        $this->assertTrue($result);
    }

    /**
     * Test: Main processing of each worker with a ping pong message
     */
    public function testProcessWithNormalMessage()
    {
        $message = (new Message())->setBody('my-message');

        $worker = $this->getAbstractWorker();
        $worker->expects($this->once())
            ->method('execute')
            ->with($message)
            ->willReturn(true);

        $result = $worker->process($message);
        $this->assertTrue($result);
    }

    /**
     * Test: Main processing of each worker in failure
     */
    public function testProcessInFailure()
    {
        $message = (new Message())->setBody('my-message');

        $worker = $this->getAbstractWorker();
        $worker->expects($this->once())
            ->method('execute')
            ->with($message)
            ->willThrowException(new \Exception());

        $result = $worker->process($message);
        $this->assertFalse($result);
    }
}
