<?php

namespace Losofacebook;
use JsonSerializable;

class Person implements JsonSerializable
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $friends = [];

    /**
     * @param array $data
     */
    private function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * @param array $data
     * @return Person
     */
    public static function create(array $data = [])
    {
        return new self($data);
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'primaryImageId' => $this->getPrimaryImageId(),
            'backgroundId' => $this->getBackgroundId(),
            'friends' => $this->getFriends(),
        ];
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->data['first_name'];
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->data['last_name'];
    }

    /**
     * @return string
     */
    public function getPrimaryImageId()
    {
        return $this->data['primary_image_id'];
    }

    /**
     * @return string
     */
    public function getBackgroundId()
    {
        return $this->data['background_id'];
    }


    /**
     * @return array
     */
    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * @param array $friends
     */
    public function setFriends(array $friends)
    {
        $this->friends = $friends;
    }

}

