<?php

namespace Tests\Fixtures\FooBundle\Model;

use JMS\Serializer\Annotation\Type;

class Task
{
    /**
     * @Type("string")
     */
    private $id;

    /**
     * @Type("string")
     */
    private $title = 'Untitled';

    /**
     * @Type("DateTime")
     */
    private $createdAt;

    /**
     * @Type("DateTime<'Y-m-d'>")
     */
    private $completedAt;

    /**
     * @Type("boolean")
     */
    private $completed = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->id = uniqid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
