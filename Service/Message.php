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
    public function __construct($body = '', array $attributes = [], $groupId = '', $deduplicationId = '')
    {
        $this->body = $body;
        $this->attributes = $attributes;
        $this->groupId = $groupId;
        $this->deduplicationId = $deduplicationId;
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param string $id
     *
     * @return Message
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
    /**
     * @param string $body
     *
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    /**
     * @return array
     */
    public function getAttributes()
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
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
    /**
     * @param string $groupId
     *
     * @return Message
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }
    /**
     * @return string
     */
    public function getDeduplicationId()
    {
        return $this->deduplicationId;
    }
    /**
     * @param string $deduplicationId
     *
     * @return Message
     */
    public function setDeduplicationId($deduplicationId)
    {
        $this->deduplicationId = $deduplicationId;
        return $this;
    }
    /**
     * @return string
     */
    public function getReceiptHandle()
    {
        return $this->receiptHandle;
    }
    /**
     * @param string $receiptHandle
     *
     * @return Message
     */
    public function setReceiptHandle($receiptHandle)
    {
        $this->receiptHandle = $receiptHandle;
        return $this;
    }
}