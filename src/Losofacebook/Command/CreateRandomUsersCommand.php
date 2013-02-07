<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Keboola\Csv\CsvFile;
use Doctrine\DBAL\Connection;

class CreateRandomUsersCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('initialize:create-random-losofaces')
            ->setDescription('Creates loads of random losofaces');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Will parse losofaces.");

        $finder = new Finder();

        $finder
            ->name('*.bz2')
            ->files()
            ->in($this->getProjectDirectory() . '/app/data/fake-names');

        foreach ($finder as $file) {

            $output->writeln('Decompressing: ' . $file->getRealpath());
            if (is_readable($file->getRealpath() . '.csv')) {
                continue;
            }



            file_put_contents(
                $file->getRealpath() . '.csv',
                bzdecompress($file->getContents())
            );

        }

        $finder = new Finder();
        $finder
            ->name('*.csv')
            ->files()
            ->in($this->getProjectDirectory() . '/app/data/fake-names');

        $db = $this->getDb();

        foreach ($finder as $file) {


            $csv = new CsvFile($file->getRealpath());



            foreach ($csv as $key => $row) {

                if ($key === 0) {
                    if (count($row) !== 35) {
                        break;
                    }

                    $headers = array_flip($row);

                    $output->writeln('Processing: ' . $file->getRealpath());
                    continue;

                }

                $dbrow = array(
                    'gender' => ($row[$headers['Gender']] == 'male') ? 1 : 2,
                    'first_name' => $row[$headers['GivenName']],
                    'middle_name' => $row[$headers['MiddleInitial']],
                    'last_name' => $row[$headers['Surname']],
                    'username' => $row[$headers['Username']],
                    'password' => $row[$headers['Password']],
                    'email' => $row[$headers['EmailAddress']],
                    'street_address' => $row[$headers['StreetAddress']],
                    'zipcode' => $row[$headers['ZipCode']],

                );

                $output->writeln("Inserting {$dbrow['first_name']} {$dbrow['last_name']}");
                try {
                    $db->insert('person', $dbrow);
                    $output->writeln("\tGreat suksee");
                } catch (\Exception $e) {
                    $output->writeln("\tMultifail");
                }


            }



        }


    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->getSilexApplication()['db'];
    }

}
