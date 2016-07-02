<?php

namespace Tests\Fixtures\FooBundle\Model;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class Task
{
    /**
     * @Type("string")
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $id;

    /**
     * @Type("string")
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $title = 'Untitled';

    /**
     * @Type("integer")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\Type(
     *     type="integer",
     *     message="{{ value }} must be numeric {{ type }}."
     * )
     * @Assert\Range(
     *     min = 0,
     *     max = 10,
     *     minMessage = "You must be at least {{ limit }}",
     *     maxMessage = "Priority max {{ limit }}"
     * )
     */
    private $priority = 1;

    /**
     * @Type("DateTime")
     * @Assert\Type("DateTime")
     */
    private $createdAt;

    /**
     * @Type("DateTime<'Y-m-d'>")
     * @Assert\Type("DateTime")
     */
    private $completedAt;

    /**
     * @Type("boolean")
     * @Assert\Type("bool")
     */
    private $completed = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->id = uniqid().'.id';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    public function getCompleted()
    {
        return $this->completed;
    }
}
