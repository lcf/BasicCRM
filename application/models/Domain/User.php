<?php
namespace Domain;

class User
{
    protected $id;

    protected $name;

    protected $email;

    protected $passwordHash;

    protected $isAdmin;

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