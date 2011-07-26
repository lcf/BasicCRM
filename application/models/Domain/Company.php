<?php
namespace Domain;

use Doctrine\Common\Collections\ArrayCollection;

/** @Entity @Table(name="companies") */
class Company
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @ManyToOne(targetEntity="Subscription") */
    protected $subscription;

    /** @Column(name="is_activated", type="boolean") */
    protected $isActivated;

    /** @OneToMany(targetEntity="User", mappedBy="company", cascade={"all"}) */
    protected $users;

    public function __construct($name, Subscription $subscription, User $admin)
    {
        $this->users = new ArrayCollection();
        if (!$name) {
            throw new \DomainException('Company name cannot be empty');
        }
        $this->name = $name;
        $this->subscription = $subscription;
        $this->isActivated = false;
        if (!$admin->isAdmin()) {
            throw new \DomainException('User must be a new admin in order to create a company');
        }
        $this->addUser($admin);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Domain\User
     */
    public function getAdmin()
    {
        foreach ($this->users as $user) {
            if ($user->isAdmin()) {
                return $user;
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfirmationCode($salt)
    {
        return sha1($this->id . $salt . $this->name);
    }

    public function isActivated()
    {
        return $this->isActivated;
    }

    public function activate($confirmationCode, $salt)
    {
        // 1. error if attempt to activate an already activated company
        if ($this->isActivated) {
            throw new \DomainException('Company\'s been activated already');
        }
        // 2. error if confirmation code is not valid
        if ($confirmationCode !== $this->getConfirmationCode($salt)) {
            throw new \DomainException('Confirmation code is not valid');
        }
        // 3. activates company
        $this->isActivated = true;
    }

    protected function addUser(User $newUser)
    {
        foreach ($this->users as $existingUser) {
            if ($existingUser == $newUser) {
                throw new \DomainException('User is in the company already');
            }
        }
        $newUser->setCompany($this);
        $this->users[] = $newUser;
    }
}