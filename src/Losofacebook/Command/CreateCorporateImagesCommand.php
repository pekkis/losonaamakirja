<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Keboola\Csv\CsvFile;
use Losofacebook\Service\ImageService;
use Losofacebook\Image;
use Doctrine\DBAL\Connection;


class CreateCorporateImagesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dev:create-corporate-images')
            ->setDescription('Creates corporate images');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Will parse corporate images.");

        $finder = new Finder();

        $finder
            ->files()
            ->in($this->getProjectDirectory() . '/app/dev/imaginarium/corporation');

        $is = $this->getImageService();

        $imageIds = [];

        foreach ($finder as $file) {
            $output->writeln("{$file->getRealpath()}");
            $imageIds[] = $is->createImage($file->getRealpath(), Image::TYPE_CORPORATE);
        }

        $db = $this->getDb();
        $companies = $db->fetchAll("SELECT * FROM company");

        $stmt = $db->prepare("UPDATE company SET primary_image_id = ? WHERE id = ?");

        foreach ($companies as $company) {
            $stmt->execute([$imageIds[array_rand($imageIds)], $company['id']]);
        }
    }

    /**
     * @return ImageService
     */
    public function getImageService()
    {
        return $this->getSilexApplication()['imageService'];
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->getSilexApplication()['db'];
    }
}
