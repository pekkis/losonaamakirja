<?php

namespace Losofacebook;

class Image extends Entity
{
    const TYPE_PERSON = 1;
    const TYPE_CORPORATE = 2;

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType()
        ];
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getType()
    {
        return $this->data['type'];
    }

}

