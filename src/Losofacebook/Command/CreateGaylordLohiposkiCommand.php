<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Keboola\Csv\CsvFile;
use Doctrine\DBAL\Connection;
use Losofacebook\Service\ImageService;
use Losofacebook\Image;

class CreateGaylordLohiposkiCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('dev:create-gaylord-lohiposki')
            ->addArgument('femaleFriends', InputArgument::OPTIONAL, 2000)
            ->setDescription('Creates Gaylord Lohiposki');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Will create Gaylord Lohiposki.");

        $db = $this->getDb();
        $db->exec("DELETE FROM person WHERE username = 'gaylord.lohiposki'");

        $imageId = $db->fetchColumn("SELECT id from image WHERE upload_path LIKE '%lohiposki%'");
        if (!$imageId) {
            $imageId = $this
                ->getImageService()
                ->createImage($this->getProjectDirectory() . '/app/dev/gaylord-lohiposki.jpg', Image::TYPE_PERSON);
        }

        $dbrow = array(
            'gender' => 1,
            'first_name' => 'Gaylord',
            'middle_name' => 'L',
            'last_name' => 'Lohiposki',
            'username' => 'gaylord.lohiposki' ,
            'password' => 'l0h1p05k1',
            'email' => 'gaylord.lohiposki@dr-kobros.com',
            'street_address' => 'Dr. Kobros Vei',
            'zipcode' => null,
            'city' => 'Nordby',
            'state' => 'Akershus',
            'country_code' => 'no',
            'country' => 'Norway',
            'telephone'  => null,
            'mothers_maiden_name' => 'Brattebratten',
            'birthday' => '1960-01-01' ,
            'occupation' => 'Interim CEO',
            'company' => 'Dr. Kobros Foundation',
            'vehicle' => 'Ferrari 458 Spider',
            'url'  => 'http://dr-kobros.com',
            'blood_type'  => 'AB',
            'weight' => 183,
            'height'  => 76,
            'latitude' => '59.936956',
            'longitude' => '10.996628',
            'background_id' => 15,
            'primary_image_id' => $imageId,
        );

        $db->insert('person', $dbrow);

        $gaylordId = $db->lastInsertId();

        $output->writeln("Will create lots of female friends for Gaylord.");

        $femaleFriends = $input->getArgument('femaleFriends');

        foreach ($db->fetchAll("SELECT id FROM person WHERE gender = 2 ORDER BY RAND() LIMIT {$femaleFriends}") as $femaleFriend) {
            $db->insert('friendship', ['target_id' => $gaylordId, 'source_id' => $femaleFriend['id']]);
        }
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->getSilexApplication()['db'];
    }

    /**
     * @return ImageService
     */
    public function getImageService()
    {
        return $this->getSilexApplication()['imageService'];
    }

}
