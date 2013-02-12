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
use DateTime;

class CreatePostCommand extends Command
{

    private $lipsums = [];

    protected function configure()
    {
        $this
            ->setName('dev:create-post')
            ->setDescription('Creates loads of posts')
            ->addArgument('count', InputArgument::REQUIRED, 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getDb();

        $min = $db->fetchColumn("SELECT MIN(id) FROM person");
        $max = $db->fetchColumn("SELECT MAX(id) FROM person");

        for ($x = 1; $x <= $input->getArgument('count'); $x = $x + 1) {

            $personId = rand($min, $max);

            try {

                $person = $this->getPersonService()->findById($personId);

                $potentials = array_merge([$person], $person->getFriends());

                // var_dump($potentials);

                $now = (new DateTime())->format('U');

                $post = [
                    'person_id' => $person->getId(),
                    'poster_id' => $potentials[array_rand($potentials)]->getId(),
                    'date_created' => DateTime::createFromFormat('U', rand($now - 500000, $now))->format('Y-m-d H:i:s'),
                    'content' => $this->getRandomLipsum(),
                ];

                $db->insert('post', $post);
                $post['id'] = $db->lastInsertId();

                $commentCount = rand(2, 30);

                $comments = [];

                for ($x = 1; $x <= $commentCount; $x = $x + 1) {

                    $comments[] = [
                        'post_id' => $post['id'],
                        'poster_id' => $potentials[array_rand($potentials)]->getId(),
                        'date_created' => DateTime::createFromFormat('U', rand($now - 500000, $now))->format('Y-m-d H:i:s'),
                        'content' => $this->getRandomLipsum(),
                    ];

                     /*
                    -> id integer unsigned NOT NULL AUTO_INCREMENT,
    -> post_id integer unsigned NOT NULL,
    -> poster_id integer unsigned NOT NULL,
    -> date_created datetime NOT NULL,
    -> content text NOT NULL,
    -> PRIMARY KEY(id),
                     */


                }

                foreach ($comments as $comment) {
                    $db->insert('comment', $comment);
                }


/*
id integer unsigned NOT NULL AUTO_INCREMENT,
person_id integer unsigned NOT NULL,
poster_id integer unsigned NOT NULL,
date_created datetime NOT NULL,
content text NOT NULL,
PRIMARY KEY(id),
FOREIGN KEY(person_id) REFERENCES person(id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY(poster_id) REFERENCES person(id) ON DELETE CASCADE ON UPDATE CASCADE
) engine=InnoDB DEFAULT CHARSET=utf8;
*/

                var_dump($post);
                var_dump($comments);


                $output->writeln("Made a post for {$personId}");

            } catch (\Exception $e) {
                $output->writeln("Friendship between {$sourceId} and {$targetId} failed");
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
            $this->lipsums['short'] = explode("\n", file_get_contents("http://loripsum.net/api/2000/short"));
            $this->lipsums['medium'] = explode("\n", file_get_contents("http://loripsum.net/api/2000/medium"));
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
