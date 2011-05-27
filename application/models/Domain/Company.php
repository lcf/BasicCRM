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

    /** @ManyToOne(targetEntity="Domain\Subscription") */
    protected $subscription;

    /** @Column(name="is_activated", type="boolean") */
    protected $isActivated;

    /** @OneToMany(targetEntity="Domain\User", mappedBy="company", cascade={"all"}) */
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