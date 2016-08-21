<?php
namespace Sentegrity\BusinessBundle\Command;

use Sentegrity\BusinessBundle\BatchJobs\Weekly;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WeeklyJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:weekly:execute')
            ->setDescription('Execute weekly job')
            ->addArgument(
                'chunk_size',
                InputArgument::REQUIRED,
                'add a chunk size'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $chunkSize = $input->getArgument('chunk_size');
        /** @var Weekly $job */
        $job = $this->getContainer()->get('sentegrity_business.batch.weekly');
        $job->execute(time(), $chunkSize);

        $output->writeln("Weekly job complete");
    }
}