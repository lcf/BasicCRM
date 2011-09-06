<?php
namespace Domain;

/** @Entity @Table(name="subscriptions") */
class Subscription
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @Column(type="integer", name="users_limit") */
    protected $usersLimit;

    /** @Column(type="integer", name="clients_limit") */
    protected $clientsLimit;

    public function getUsersLimit()
    {
        return $this->usersLimit;
    }

    public function getClientsLimit()
    {
        return $this->clientsLimit;
    }
}