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
    private $receiptHandle;

    /**
     * Message constructor.
     *
     * @param string $body
     * @param array $attributes
     */
    public function __construct(string $body = '', array $attributes = [])
    {
        $this->body = $body;
        $this->attributes = $attributes;
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

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
     * @return $this
     */
    public function setReceiptHandle(string $receiptHandle)
    {
        $this->receiptHandle = $receiptHandle;

        return $this;
    }
}
