<?php

namespace Losofacebook;

class Post extends Entity
{
    private $person;

    private $comments = [];

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'person' => $this->getPerson(),
            'comments' => $this->getComments(),
            'date_created' => $this->getDateCreated(),
            'content' => $this->getContent(),
        ];
    }

    public function setPerson(Person $person)
    {
        $this->person = $person;
    }

    public function getPerson()
    {
        return $this->person;
    }


    public function setComments(array $comments)
    {
        $this->comments = $comments;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getDateCreated()
    {
        return $this->data['date_created'];
    }

    public function getContent()
    {
        return $this->data['content'];
    }

}

