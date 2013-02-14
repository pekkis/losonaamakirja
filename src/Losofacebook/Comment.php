<?php

namespace Losofacebook;

class Comment extends Entity
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
            'date_created' => $this->getDateCreated(),
            'content' => $this->getContent(),
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

    public function getDateCreated()
    {
        return $this->data['date_created'];
    }

    public function getContent()
    {
        return $this->data['content'];
    }

}

