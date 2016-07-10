<?php
namespace Sentegrity\BusinessBundle\Command;

use Sentegrity\BusinessBundle\Entity\Documents\AdminUser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sentegrity\BusinessBundle\Services\Support\Password;

class CreateSuperadminCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sentegrity:generate:superadmin')
            ->setDescription('Execute push queue')
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Add a password for superadmin'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $password = $input->getArgument('password');

        $user = new AdminUser();
        $user->setOrganization(null)
            ->setPermission(0)
            ->setUsername('superadmin')
            ->setPassword(Password::seedAndEncryptPassword($password));

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $output->writeln("Superadmin generated");
    }
}