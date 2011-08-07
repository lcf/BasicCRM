<?php
namespace Domain;

class SessionsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getValid($id)
    {
        $session = $this->find($id);
        if (!$session) {
            throw new \DomainException('Session is not found');
        }
        if (!$session->isValid()) {
            throw new \DomainException('Session is no longer valid');
        }
        $session->refresh();
        $this->_em->persist($session);

        return $session;
    }
}
