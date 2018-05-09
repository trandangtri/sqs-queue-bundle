<?php

namespace TriTran\SqsQueueBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use TriTran\SqsQueueBundle\Service\Message;

/**
 * Class MessageTest
 * @package TriTran\SqsQueueBundle\Tests\Unit\Service
 */
class MessageTest extends TestCase
{
    /**
     * Test: Construction
     */
    public function testConstruction()
    {
        $body = 'my-body';
        $attributes = ['a', 'b', 'c'];
        $groupId = 'group-id';
        $deduplicationID = 'deduplication-id';
        $message = new Message($body, $attributes, $groupId, $deduplicationID);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($body, $message->getBody());
        $this->assertEquals($attributes, $message->getAttributes());
        $this->assertEquals($groupId, $message->getGroupId());
        $this->assertEquals($deduplicationID, $message->getDeduplicationId());
    }

    /**
     * Test: Getter/Setter
     */
    public function testGetterSetter()
    {
        $message = new Message('', [], '', '');

        $this->assertInstanceOf(Message::class, $message->setId('my-id'));
        $this->assertEquals('my-id', $message->getId());
        $this->assertInstanceOf(Message::class, $message->setBody('my-body'));
        $this->assertEquals('my-body', $message->getBody());
        $this->assertInstanceOf(Message::class, $message->setAttributes(['a', 'b', 'c']));
        $this->assertEquals(['a', 'b', 'c'], $message->getAttributes());
        $this->assertInstanceOf(Message::class, $message->setReceiptHandle('my-receipt'));
        $this->assertEquals('my-receipt', $message->getReceiptHandle());
        $this->assertInstanceOf(Message::class, $message->setGroupId('group-id'));
        $this->assertEquals('group-id', $message->getGroupId());
        $this->assertInstanceOf(Message::class, $message->setDeduplicationId('deduplication-id'));
        $this->assertEquals('deduplication-id', $message->getDeduplicationId());
    }
}
