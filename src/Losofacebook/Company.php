<?php

namespace Losofacebook;

class Company extends Entity
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'primaryImageId' => $this->getPrimaryImageId(),
            'backgroundId' => $this->getBackgroundId(),
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
    public function getName()
    {
        return $this->data['name'];
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

}

