<?php

namespace TriTran\SqsQueueBundle\Service\Worker;

use TriTran\SqsQueueBundle\Service\Message;

/**
 * Class AbstractWorker
 * @package TriTran\SqsQueueBundle\Service\Worker
 */
abstract class AbstractWorker
{
    /** @var string  Worker error message after processing. */
    private $error = null;

    /**
     * @param Message $message
     *
     * @return bool
     */
    final public function process(Message $message)
    {
        if ($message->getBody() === 'ping') {
            echo 'Pong. Now is ' . (new \DateTime('now'))->format('M d, Y H:i:s') . PHP_EOL;

            return true;
        }

        $this->preExecute($message);
        try {
            $result = $this->execute($message);
        } catch (\Exception $e) {
            $result = false;
            $this->error = $e->getMessage();
        }
        $this->postExecute($message);

        // Let worker does something on success or failure
        $result === true ? $this->onSucceeded() : $this->onFailed();

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
     * Do something via post execution. It is better to proceed with task related to message.
     *
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
    abstract protected function execute(Message $message);

    /**
     * Event fired when worker has processed message successfully.
     *
     * @return void
     */
    abstract protected function onSucceeded();

    /**
     * Event fired when worker has failed to process message.
     *
     * @return void
     */
    abstract protected function onFailed();

    /**
     * Check if worker has error after processing.
     * By default, error is set to <code>null</code>.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->error !== null;
    }

    /**
     * Get worker error message.
     *
     * @return string
     */
    public function error()
    {
        return $this->error;
    }
}
