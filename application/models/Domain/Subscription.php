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
}