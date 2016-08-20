<?php
namespace Sentegrity\BusinessBundle\Command;

use Sentegrity\BusinessBundle\BatchJobs\Daily;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:daily:execute')
            ->setDescription('Execute push queue')
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
        /** @var Daily $job */
        $job = $this->getContainer()->get('sentegrity_business.batch.daily');
        $job->execute(time(), $chunkSize);

        $output->writeln("Daily job complete");
    }
}