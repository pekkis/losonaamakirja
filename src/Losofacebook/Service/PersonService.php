<?php

namespace Losofacebook\Service;
use Doctrine\DBAL\Connection;
use Losofacebook\Person;

/**
 * Image service
 */
class PersonService
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @param $basePath
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Uploads image
     *
     * @param $path
     */
    public function findByUsername($username, $findFriends = true)
    {
        $data = $this->conn->fetchAssoc(
            "SELECT * FROM person WHERE username = ?", [$username]
        );

        if (!$data) {
            return false;
        }

        $person = Person::create($data);

        if ($findFriends) {
            $person->setFriends($this->findFriends($person->getId()));
        }

        return $person;
    }

    public function findById($id, $findFriends = true)
    {
        $data = $this->conn->fetchAssoc(
            "SELECT * FROM person WHERE id = ?", [$id]
        );

        if (!$data) {
            return false;
        }

        $person = Person::create($data);

        if ($findFriends) {
            $person->setFriends($this->findFriends($person->getId()));
        }

        return $person;
    }


    public function findFriends($id)
    {
        $friends = [];
        foreach ($this->findFriendIds($id) as $friendId) {
            $friends[] = $this->findById($friendId, false);
        }
        return $friends;
    }


    public function findFriendIds($id)
    {
        $myAdded = $this->conn->fetchAll(
            "SELECT target_id FROM friendship WHERE source_id = ?",
            [$id]
        );

        $meAdded = $this->conn->fetchAll(
            "SELECT source_id FROM friendship WHERE target_id = ?",
            [$id]
        );

        $myAdded = array_reduce($myAdded, function ($result, $row) {
            $result[] = $row['target_id'];
            return $result;
        }, []);

        $meAdded = array_reduce($meAdded, function ($result, $row) {
            $result[] = $row['source_id'];
            return $result;
        }, []);

        return array_unique(array_merge($myAdded, $meAdded));
    }

    public function addFriend($friendId)
    {

    }

    public function removeFriend($friendId)
    {

    }



}
