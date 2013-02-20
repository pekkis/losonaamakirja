<?php

namespace Losofacebook;

class Post extends Entity
{
    private $poster;

    private $comments = [];

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'poster' => $this->getPoster(),
            'comments' => $this->getComments(),
            'date_created' => $this->getDateCreated(),
            'content' => $this->getContent(),
            'id' => $this->getId(),
        ];
    }

    public function setPoster(Person $poster)
    {
        $this->poster = $poster;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function getId()
    {
        return $this->data['id'];
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

    public function getPersonId()
    {
        return $this->data['person_id'];
    }


}

