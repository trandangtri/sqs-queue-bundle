<?php

namespace TriTran\SqsQueueBundle\Tests\app\Worker;

use TriTran\SqsQueueBundle\Service\Message;
use TriTran\SqsQueueBundle\Service\Worker\AbstractWorker;

class BasicWorker extends AbstractWorker
{
    /**
     * @param Message $message
     *
     * @return boolean
     */
    protected function execute(Message $message)
    {
        return true;
    }

    /**
     * Handle worker on success.
     */
    protected function onSucceeded()
    {

    }

    /**
     * Handle worker on failure.
     */
    protected function onFailed()
    {

    }
}