<?php
namespace Domain;

/** @Entity @Table(name="users") */
class User
{
    const PASSWORD_MINIMAL_LENGTH = 6;

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

    /** @ManyToOne(targetEntity="Company", inversedBy="users") */
    protected $company;

    public function __construct($email, $name, $password, $isAdmin = false)
    {
        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new \DomainException('Email is not valid');
        }
        $this->setPassword($password);
        $this->email = $email;
        $this->name = $name;
        $this->isAdmin = $isAdmin;
    }

    public function setPassword($password)
    {
        if (self::PASSWORD_MINIMAL_LENGTH > strlen($password)) {
            throw new \DomainException('Wrong password length');
        }
        $this->passwordHash = $this->calculatePasswordHash($password);
    }

    public function isPasswordValid($password)
    {
        return ($this->calculatePasswordHash($password) == $this->passwordHash);
    }

    public function isActivated()
    {
        return $this->company->isActivated();
    }

    protected function calculatePasswordHash($password)
    {
        return sha1($password);
    }

    public function getId()
    {
        return $this->id;
    }

    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function revokeAdmin()
    {
        if (!$this->isAdmin) {
            throw new \DomainException('User is not an admin');
        }
        $this->isAdmin = false;
    }

    public function grantAdmin()
    {
        if ($this->isAdmin) {
            throw new \DomainException('User is already an admin');
        }
        $this->isAdmin = true;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
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