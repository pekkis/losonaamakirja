<?php

namespace Losofacebook;

class Comment extends Entity
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

    public function getDateCreated()
    {
        return $this->data['date_created'];
    }

    public function getContent()
    {
        return $this->data['content'];
    }

}

