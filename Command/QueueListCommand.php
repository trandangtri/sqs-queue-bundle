<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TriTran\SqsQueueBundle\Service\QueueManager;

/**
 * Class QueueListCommand
 * @package TriTran\SqsQueueBundle\Command
 */
class QueueListCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:list')
            ->addOption(
                'prefix',
                null,
                InputOption::VALUE_REQUIRED,
                'Queues with a name that begins with the specified value are returned.',
                ''
            )
            ->setDescription('Returns a list of your queues.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Start getting the list of existing queues in SQS');

        /** @var QueueManager $queueManager */
        $queueManager = $this->container->get('tritran.sqs_queue.queue_manager');
        $result = $queueManager->listQueue($input->getOption('prefix'));

        if (empty($result)) {
            $io->text('You don\'t have any queue at this moment. Please go to AWS Console to create a new one.');
        } else {
            $io->table(['Queue URL'], array_map(function ($value) {
                return [$value];
            }, $result));
        }

        $io->success('Done');
    }
}
