<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TriTran\SqsQueueBundle\Service\BaseQueue;
use TriTran\SqsQueueBundle\Service\QueueManager;

/**
 * Class QueueUpdateCommand.
 */
class QueueUpdateCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:update')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Set this parameter to execute this command'
            )
            ->setDescription('Update Queue attribute based on configuration');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('force')) {
            $io->note('Option --force is mandatory to update data.');
            $io->warning('This action should not be used in the production environment.');

            return;
        }

        if (!$this->container->hasParameter('tritran.sqs_queue.queues')) {
            $io->warning('Queue Configuration is missing.');

            return;
        }

        /** @var QueueManager $queueManager */
        $queueManager = $this->container->get('tritran.sqs_queue.queue_manager');
        $awsQueues = $queueManager->listQueue();

        /** @var array $localQueues */
        $localQueues = $this->container->getParameter('tritran.sqs_queue.queues');
        foreach ($localQueues as $queueName => $queueOption) {
            if (in_array($queueOption['queue_url'], $awsQueues, true)) {
                $io->text(sprintf('We will update <comment>%s</comment>', $queueOption['queue_url']));

                /** @var BaseQueue $queue */
                $queue = $this->container->get(sprintf('tritran.sqs_queue.%s', $queueName));
                $queueManager->setQueueAttributes($queue->getQueueUrl(), $queue->getAttributes());

                $io->table(['Attribute Name', 'Value'], array_map(function ($k, $v) {
                    return [$k, $v];
                }, array_keys($queue->getAttributes()), $queue->getAttributes()));
            }
        }

        $io->success('Done');
    }
}
