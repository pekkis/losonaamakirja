<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateRandomUsersCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('initialize:create-random-losofaces')
            ->setDescription('Creates loads of random losofaces')
            ->addArgument(
                'count',
                InputArgument::REQUIRED,
                'How many losofaces to create?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $input->getArgument('count');

        $output->writeln("Will create {$count} losofaces.");
    }

}
