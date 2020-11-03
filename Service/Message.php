<?php

namespace TriTran\SqsQueueBundle\Service;

/**
 * Class Message
 * @package TriTran\SqsQueueBundle\Service
 */
class Message
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $messageAttributes;

    /**
     * @var string
     */
    private $groupId;

    /**
     * @var string
     */
    private $deduplicationId;

    /**
     * @var string
     */
    private $receiptHandle;

    /**
     * Message constructor.
     *
     * @param string $body
     * @param array $attributes
     * @param string $groupId
     * @param string $deduplicationId
     */
    public function __construct(
        string $body = '',
        array $attributes = [],
        array $messageAttributes = [],
        string $groupId = '',
        string $deduplicationId = ''
    ) {
        $this->body = $body;
        $this->attributes = $attributes;
        $this->messageAttributes = $messageAttributes;
        $this->groupId = $groupId;
        $this->deduplicationId = $deduplicationId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Message
     */
    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return Message
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return Message
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessageAttributes(): array
    {
        return $this->messageAttributes;
    }

    /**
     * @param array $messageAttributes
     *
     * @return Message
     */
    public function setAttributes(array $messageAttributes)
    {
        $this->messageAttributes = $messageAttributes;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     *
     * @return Message
     */
    public function setGroupId(string $groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeduplicationId(): string
    {
        return $this->deduplicationId;
    }

    /**
     * @param string $deduplicationId
     *
     * @return Message
     */
    public function setDeduplicationId(string $deduplicationId)
    {
        $this->deduplicationId = $deduplicationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getReceiptHandle(): string
    {
        return $this->receiptHandle;
    }

    /**
     * @param string $receiptHandle
     *
     * @return Message
     */
    public function setReceiptHandle(string $receiptHandle)
    {
        $this->receiptHandle = $receiptHandle;

        return $this;
    }
}
