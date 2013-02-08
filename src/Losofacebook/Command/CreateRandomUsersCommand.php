<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Keboola\Csv\CsvFile;
use Doctrine\DBAL\Connection;
use DateTime;

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

        $db->exec("DELETE FROM person");

        foreach ($finder as $file) {

            $csv = new CsvFile($file->getRealpath());

            foreach ($csv as $key => $row) {

                if ($key === 0) {
                    if (count($row) < 34) {
                        break;
                    }

                    $headers = array_flip($row);

                    $output->writeln('Processing: ' . $file->getRealpath());
                    continue;

                }

                $bd = DateTime::createFromFormat('n/j/Y', $row[$headers['Birthday']]) ?: new DateTime('1978-03-21');

                $country = isset($headers['CountryFull']) ? $row[$headers['CountryFull']] : 'us';

                $dbrow = array(
                    'gender' => ($row[$headers['Gender']] == 'male') ? 1 : 2,
                    'first_name' => $row[$headers['GivenName']],
                    'middle_name' => $row[$headers['MiddleInitial']],
                    'last_name' => $row[$headers['Surname']],
                    'username' => $row[$headers['Username']] . uniqid("") ,
                    'password' => $row[$headers['Password']],
                    'email' => $row[$headers['EmailAddress']],
                    'street_address' => $row[$headers['StreetAddress']],
                    'zipcode' => $row[$headers['ZipCode']],
                    'city' => $row[$headers['City']],
                    'state' => $row[$headers['State']],
                    'country_code' => $row[$headers['Country']],
                    'country' => $country,
                    'telephone'  => $row[$headers['TelephoneNumber']],
                    'mothers_maiden_name' => $row[$headers['MothersMaiden']],
                    'birthday' => $bd->format('Y-m-d') ,
                    'occupation' => $row[$headers['Occupation']],
                    'company' => $row[$headers['Company']],
                    'vehicle' => $row[$headers['Vehicle']],
                    'url'  => $row[$headers['Domain']],
                    'blood_type'  => $row[$headers['BloodType']],
                    'weight' => $row[$headers['Kilograms']],
                    'height'  => $row[$headers['Centimeters']],
                    'latitude' => $row[$headers['Latitude']],
                    'longitude' => $row[$headers['Longitude']],
                );

                $output->writeln("Inserting {$dbrow['first_name']} {$dbrow['last_name']}");
                try {
                    $db->insert('person', $dbrow);
                    $output->writeln("\tGreat suksee");
                } catch (\Exception $e) {
                    $output->writeln("\tMultifail");
                    echo $e;
                    sleep(5);
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
