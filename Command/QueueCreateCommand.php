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
use TriTran\SqsQueueBundle\Service\QueueManager;

/**
 * Class QueueCreateCommand.
 */
class QueueCreateCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:create')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'Queue ID which you want to send message'
            )
            ->addOption(
                'delay_seconds',
                null,
                InputOption::VALUE_REQUIRED,
                'DelaySeconds',
                0
            )
            ->addOption(
                'maximum_message_size',
                null,
                InputOption::VALUE_REQUIRED,
                'MaximumMessageSize',
                262144
            )
            ->addOption(
                'message_retention_period',
                null,
                InputOption::VALUE_REQUIRED,
                'MessageRetentionPeriod',
                345600
            )
            ->addOption(
                'receive_message_wait_time_seconds',
                null,
                InputOption::VALUE_REQUIRED,
                'ReceiveMessageWaitTimeSeconds',
                0
            )
            ->addOption(
                'visibility_timeout',
                null,
                InputOption::VALUE_REQUIRED,
                'VisibilityTimeout',
                30
            )
            ->addOption(
                'content_based_deduplication',
                null,
                InputOption::VALUE_NONE,
                'ContentBasedDeduplication'
            )
            ->setDescription('Create a queue by name and basic attributions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name');
        if ($this->container->has(sprintf('tritran.sqs_queue.%s', $queueName))) {
            throw new \InvalidArgumentException(sprintf('Queue [%s] exists. Please use another name.', $queueName));
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Start creating a new queue which name is <comment>%s</comment>', $queueName));

        /** @var QueueManager $queueManager */
        $queueManager = $this->container->get('tritran.sqs_queue.queue_manager');
        $queueUrl = $queueManager->createQueue($queueName, [
            'DelaySeconds' => $input->getOption('delay_seconds'),
            'MaximumMessageSize' => $input->getOption('maximum_message_size'),
            'MessageRetentionPeriod' => $input->getOption('message_retention_period'),
            'ReceiveMessageWaitTimeSeconds' => $input->getOption('receive_message_wait_time_seconds'),
            'VisibilityTimeout' => $input->getOption('visibility_timeout'),
            'ContentBasedDeduplication' => $input->getOption('content_based_deduplication'),
        ]);

        $io->text(sprintf('Created successfully. New Queue URL: <comment>%s</comment>', $queueUrl));
        $io->success('Done');
    }
}
