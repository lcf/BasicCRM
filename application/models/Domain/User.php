<?php
namespace Domain;

/** @Entity @Table(name="users") */
class User
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** @Column(type="string") */
    protected $name;

    /** @Column(type="string") */
    protected $email;

    /** @Column(name="password_hash", type="string") */
    protected $passwordHash;

    /** @Column(name="is_admin", type="boolean") */
    protected $isAdmin;

    /** @ManyToOne(targetEntity="Domain\Company", inversedBy="users") */
    protected $company;

    public function __construct($email, $name, $password, $isAdmin = false)
    {
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new \DomainException('Email is not valid');
        }
        if (6 > strlen($password)) {
            throw new \DomainException('Wrong password length');
        }
        $this->email = $email;
        $this->name = $name;
        $this->passwordHash = sha1($password);
        $this->isAdmin = $isAdmin;
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function setCompany(Company $company)
    {
        if (!$this->company) {
            $this->company = $company;
        } else {
            throw new \DomainException('The user already belongs to a company');
        }
    }
}