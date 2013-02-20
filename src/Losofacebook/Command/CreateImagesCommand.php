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

use DateTime;

class CreateImagesCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('dev:create-images')
            ->setDescription('Creates images for users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Will recreate images.");

        $db = $this->getDb();
        $imageService = $this->getImageService();
                
        $images = $db->fetchAll("SELECT * FROM image WHERE type = 1");
        
        foreach ($images as $image) {
            $imageService->createVersions($image['id']);
            $output->writeln("Recreating versions for #{$image['id']}");
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
