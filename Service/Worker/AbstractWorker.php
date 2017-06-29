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
    final public function run(Message $message)
    {
        if ($message->getBody() === 'ping') {
            echo 'Pong. Now is ' . (new \DateTime('now'))->format('M d, Y H:i:s') . PHP_EOL;

            return true;
        }

        $this->preExecute($message);
        $result = $this->execute($message);
        $this->postExecute($message);

        return $result;
    }

    /**
     * @param Message $message
     */
    protected function preExecute(Message $message)
    {
        // TODO: do something here
    }

    /**
     * @param Message $message
     */
    protected function postExecute(Message $message)
    {
        // TODO: do something here
    }

    /**
     * @param Message $message
     *
     * @return boolean
     */
    abstract protected function execute(Message $message);
}
