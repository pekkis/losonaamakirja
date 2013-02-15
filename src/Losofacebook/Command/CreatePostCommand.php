<?php

namespace Losofacebook\Command;

use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Keboola\Csv\CsvFile;
use Doctrine\DBAL\Connection;
use Losofacebook\Service\PersonService;
use Losofacebook\Person;
use DateTime;

class CreatePostCommand extends Command
{

    private $lipsums = [];

    protected function configure()
    {
        $this
            ->setName('dev:create-post')
            ->setDescription('Creates loads of posts')
            ->addArgument('count', InputArgument::REQUIRED, 1)
            ->addArgument('username', InputArgument::REQUIRED, null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $input->getArgument('count');
        $username = $input->getArgument('username');

        $person = $this->getPersonService()->findByUsername($username);

        $this->createPosts($person->getId(), $output, $count);
        foreach ($person->getFriends() as $friend) {
            $this->createPosts($friend->getId(), $output, $count);
        }
    }


    private function createPosts($personId, OutputInterface $output, $count)
    {
        $person = $this->getPersonService()->findById($personId, true);

        $output->write('Creating ' . $count . ' posts for ' . $person->getFirstName() . ' ' . $person->getLastName());

        $db = $this->getDb();

        for ($posts = 1; $posts <= $count; $posts = $posts + 1) {

            try {

                $potentials = array_merge([$person], $person->getFriends());

                // var_dump($potentials);

                $now = (new DateTime())->format('U');

                if (rand(1, 10) >= 8) {
                    $posterId = $potentials[array_rand($potentials)]->getId();
                } else {
                    $posterId = $person->getId();
                }

                $post = [
                    'person_id' => $person->getId(),
                    'poster_id' => $posterId,
                    'date_created' => DateTime::createFromFormat('U', rand($now - 500000, $now))->format('Y-m-d H:i:s'),
                    'content' => $this->getRandomLipsum(),
                ];

                $db->insert('post', $post);
                $post['id'] = $db->lastInsertId();

                $commentCount = rand(2, 30);

                $comments = [];

                for ($x = 1; $x <= $commentCount; $x = $x + 1) {

                    if (rand(1, 10) >= 4) {
                        $posterId = $potentials[array_rand($potentials)]->getId();
                    } else {
                        $posterId = $person->getId();
                    }

                    $comments[] = [
                        'post_id' => $post['id'],
                        'poster_id' => $posterId,
                        'date_created' => DateTime::createFromFormat('U', rand($now - 500000, $now))->format('Y-m-d H:i:s'),
                        'content' => $this->getRandomLipsum(),
                    ];

                }

                foreach ($comments as $comment) {
                    $db->insert('comment', $comment);
                }

                $output->writeln("Made a post for {$person->getId()}");

            } catch (\Exception $e) {
                $output->writeln("Failed");
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

    /**
     * @return PersonService
     */
    public function getPersonService()
    {
        return $this->getSilexApplication()['personService'];
    }


    protected function getRandomLipsum()
    {
        if (!$this->lipsums) {
            $this->lipsums['short'] = explode("\n\n", file_get_contents("http://loripsum.net/api/2000/short"));
            $this->lipsums['medium'] = explode("\n\n", file_get_contents("http://loripsum.net/api/2000/medium"));

            array_pop($this->lipsums['short']);
            array_pop($this->lipsums['medium']);

        }

        $lengths = ['short', 'medium'];
        $paragraphs = rand(1, 3);

        $lipsum = [];

        for ($x = 1; $x <= $paragraphs; $x = $x + 1) {
            $length = $lengths[array_rand($lengths)];
            $lipsum[] = $this->lipsums[$length][array_rand($this->lipsums[$length])];
        }

        return implode('', $lipsum);
    }

}
