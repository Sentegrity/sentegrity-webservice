<?php
namespace Sentegrity\BusinessBundle\Command;

use Sentegrity\BusinessBundle\BatchJobs\Cleaner;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('batch:clean:execute')
            ->setDescription('Execute daily job')
            ->addArgument(
                'resource',
                InputArgument::REQUIRED,
                'Enter row or table'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resource = $input->getArgument('resource');
        /** @var Cleaner $job */
        $job = $this->getContainer()->get('sentegrity_business.batch.cleaner');
        $job->execute($resource);

        $output->writeln("Clean up job complete");
    }
}