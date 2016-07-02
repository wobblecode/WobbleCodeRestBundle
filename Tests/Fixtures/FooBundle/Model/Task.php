<?php

namespace Tests\Fixtures\FooBundle\Model;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

class Task
{
    /**
     * Task Unique Id
     *
     * @Type("string")
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $id;

    /**
     * Title of the task
     *
     * @Type("string")
     * @Assert\Type("string")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $title = 'Untitled';

    /**
     * Pirority or Importance
     *
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
     * @Assert\Type("boolean")
     */
    private $completed = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->id = uniqid().'.id';
    }

    /**
     * Get the value of Task Unique Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of Task Unique Id
     *
     * @param mixed id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of Title of the task
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the value of Title of the task
     *
     * @param mixed title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of Pirority or Importance
     *
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set the value of Pirority or Importance
     *
     * @param mixed priority
     *
     * @return self
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get the value of Created At
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of Created At
     *
     * @param mixed createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of Completed At
     *
     * @return mixed
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * Set the value of Completed At
     *
     * @param mixed $completedAt
     *
     * @return self
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get the value of Completed
     *
     * @return mixed
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * Set the value of Completed
     *
     * @param mixed $completed
     *
     * @return self
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;

        return $this;
    }
}
