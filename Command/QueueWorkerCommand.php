<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Service\BaseWorker;

/**
 * Class QueueWorkerCommand.
 */
class QueueWorkerCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:worker')
            ->addArgument('name', InputArgument::REQUIRED, 'Queue Name', null)
            ->addOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Messages to consume', 0)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max messages to consume per request', 1)
            ->setDescription('Start a worker that will listen to a specified SQS queue');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name');
        if (!$this->container->has(sprintf('tritran.sqs_queue.%s', $queueName))) {
            throw new \InvalidArgumentException(sprintf('Queue [%s] does not exist.', $queueName));
        }
        $amount = $input->getOption('messages');
        if ($amount < 0) {
            throw new \InvalidArgumentException('The -m option should be null or greater than 0');
        }

        $limit = $input->getOption('limit');
        if ($limit < 1) {
            throw new \InvalidArgumentException('The -l option should be null or greater than 1');
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Start listening to queue <comment>%s</comment>', $queueName));

        /** @var BaseQueue $queue */
        $queue = $this->container->get(sprintf('tritran.sqs_queue.%s', $queueName));

        /** @var BaseWorker $worker */
        $worker = $this->container->get('tritran.sqs_queue.queue_worker');
        $worker->start($queue, $amount, $limit);
    }
}
