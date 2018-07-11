<?php

namespace TriTran\SqsQueueBundle\Service\Worker;

use TriTran\SqsQueueBundle\Service\Message;

/**
 * Class AbstractWorker
 * @package TriTran\SqsQueueBundle\Service\Worker
 */
abstract class AbstractWorker
{
    /**
     * @param Message $message
     *
     * @return bool
     */
    public final function process(Message $message)
    {
        if ($message->getBody() === 'ping') {
            echo 'Pong. Now is ' . (new \DateTime('now'))->format('M d, Y H:i:s') . PHP_EOL;
            return true;
        }
        $this->preExecute($message);
        try {
            $result = $this->execute($message);
        } catch (\Exception $e) {
            return false;
        }
        $this->postExecute($message);
        return $result;
    }
    /**
     * @param Message $message
     */
    protected function preExecute(Message $message)
    {
        // Do something here
    }
    /**
     * @param Message $message
     */
    protected function postExecute(Message $message)
    {
        // Do something here
    }
    /**
     * @param Message $message
     *
     * @return boolean
     */
    protected abstract function execute(Message $message);
}