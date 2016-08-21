<?php
namespace Sentegrity\BusinessBundle\Command;

use Sentegrity\BusinessBundle\BatchJobs\Monthly;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MonthlyJobCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:monthly:execute')
            ->setDescription('Execute monthly job')
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
        /** @var Monthly $job */
        $job = $this->getContainer()->get('sentegrity_business.batch.monthly');
        $job->execute(time(), $chunkSize);

        $output->writeln("Monthly job complete");
    }
}