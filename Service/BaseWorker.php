<?php

namespace TriTran\SqsQueueBundle\Service;

/**
 * Class BaseWorker
 * @package TriTran\SqsQueueBundle\Service
 */
class BaseWorker
{
    /**
     * @param BaseQueue $queue
     * @param int $limit Zero is all
     */
    public function start(BaseQueue $queue, int $limit = 1)
    {
        $this->consume($queue, $limit);
    }

    /**
     * @param BaseQueue $queue
     * @param int $limit
     */
    private function consume(BaseQueue $queue, int $limit = 1)
    {
        while (true) {
            $this->fetchMessage($queue, $limit);
        }
    }

    /**
     * @param BaseQueue $queue
     * @param int $limit
     */
    private function fetchMessage(BaseQueue $queue, int $limit = 1)
    {
        $consumer = $queue->getQueueWorker();

        /** @var MessageCollection $result */
        $messages = $queue->receiveMessage($limit);

        $messages->rewind();
        while ($messages->valid()) {
            /** @var Message $message */
            $message = $messages->current();
            $result = $consumer->process($message);
            if ($result !== false) {
                $queue->deleteMessage($message);
            } else {
                $queue->releaseMessage($message);
            }

            $messages->next();
        }
    }
}
