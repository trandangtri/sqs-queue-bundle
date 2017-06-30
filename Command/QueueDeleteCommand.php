<?php

namespace TriTran\SqsQueueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TriTran\SqsQueueBundle\Service\QueueManager;

/**
 * Class QueueDeleteCommand
 * @package TriTran\SqsQueueBundle\Command
 */
class QueueDeleteCommand extends ContainerAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('tritran:sqs_queue:delete')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Queue Url which you want to remove'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Set this parameter to execute this command'
            )
            ->setDescription('Create a queue by name and basic attributions');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if (!$input->getOption('force')) {
            $io->note('Option --force is mandatory to drop data.');
            $io->warning('This action should not be used in the production environment.');
            return;
        }

        $queueUrl = $input->getArgument('url');

        $io->title(sprintf('Start deleting the specified queue by URL <comment>%s</comment>', $queueUrl));

        /** @var QueueManager $queueManager */
        $queueManager = $this->getContainer()->get('tritran.sqs_queue.queue_manager');
        $queueManager->deleteQueue($queueUrl);

        $io->text('Deleted successfully');
        $io->success('Done');
    }
}
