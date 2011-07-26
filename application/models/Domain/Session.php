<?php
namespace Domain;

/**
 * @Entity(repositoryClass="Domain\SessionsRepository")
 * @Table(name="sessions")
 */
class Session
{
    const LIFETIME_DAYS = 1;

    /**
     * @Id @Column(type="string")
     * @GeneratedValue(strategy="NONE")
     */
    protected $id;

    /** @ManyToOne(targetEntity="User") */
    protected $user;

    /**
     * @Column(type="datetime")
     * @var \DateTime
     */
    protected $modified;

    public function __construct(User $user)
    {
        $this->id = md5(uniqid());
        $this->user = $user;
        $this->refresh();
    }

    public function getId()
    {
        return $this->id;
    }

    public function isValid()
    {
        $now = new \DateTime();
        // where '%a' is total amount of days
        return $now->diff($this->modified)->format('%a') <= self::LIFETIME_DAYS;
    }

    public function refresh()
    {
        $this->modified = new \DateTime();
    }

    public function getUser()
    {
        return $this->user;
    }
}