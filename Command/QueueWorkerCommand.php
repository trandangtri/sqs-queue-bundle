<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Service\BaseWorker;

/**
 * Class QueueWorkerCommand
 * @package TriTran\SqsQueueBundle\Command
 */
class QueueWorkerCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:worker')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Queue Name',
                null
            )
            ->setDescription('Start a worker that will listen to a specified SQS queue');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name');
        if (!$this->getContainer()->has(sprintf('tritran.sqs_queue.%s', $queueName))) {
            throw new \InvalidArgumentException(sprintf('Queue [%s] does not exist.', $queueName));
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Start listening to queue <comment>%s</comment>', $queueName));

        /** @var BaseQueue $queue */
        $queue = $this->getContainer()->get(sprintf('tritran.sqs_queue.%s', $queueName));

        /** @var BaseWorker $worker */
        $worker = $this->getContainer()->get('tritran.sqs_queue.queue_worker');
        $worker->start($queue);
    }
}
