<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TriTran\SqsQueueBundle\Service\BaseQueue;

/**
 * Class QueuePingCommand
 * @package TriTran\SqsQueueBundle\Command
 */
class QueuePingCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:ping')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Queue ID which you want to send message'
            )
            ->setDescription('Send a simply message to a queue, for DEBUG only');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name');
        if (!$this->container->has(sprintf('tritran.sqs_queue.%s', $queueName))) {
            throw new \InvalidArgumentException(sprintf('Queue [%s] does not exist.', $queueName));
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Start sending a Hello message to SQS <comment>%s</comment>', $queueName));

        /** @var BaseQueue $queue */
        $queue = $this->container->get(sprintf('tritran.sqs_queue.%s', $queueName));
        $messageId = $queue->ping();

        $io->text(sprintf('Sent successfully. MessageID: <comment>%s</comment>', $messageId));
        $io->success('Done');
    }
}
