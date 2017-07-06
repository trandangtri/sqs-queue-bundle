<?php

namespace TriTran\SqsQueueBundle\Tests\app;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class KernelTestCase
 * @package TriTran\SqsQueueBundle\Tests
 */
class KernelTestCase extends SymfonyKernelTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns service container.
     *
     * @param bool $reinitialize Force kernel reinitialization.
     * @param array $kernelOptions Options used passed to kernel if it needs to be initialized.
     *
     * @return ContainerInterface
     */
    protected function getContainer($reinitialize = false, $kernelOptions = [])
    {
        if ($this->container === null || $reinitialize) {
            static::bootKernel($kernelOptions);
            $this->container = static::$kernel->getContainer();
        }

        return $this->container;
    }

    /**
     * @param ContainerAwareCommand $command
     *
     * @return CommandTester
     */
    public function createCommandTester(ContainerAwareCommand $command)
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $command->setContainer($this->getContainer());
        $application->add($command);

        $command = $application->find($command->getName());

        return new CommandTester($command);
    }
}
