<?php
namespace Services;

use Domain\Session,
    Domain\User;

class AuthService
{
    public function loginUser($email, $password)
    {
        $usersRepository = \ServiceLocator::getUsersRepository();
        $entityManager = \ServiceLocator::getEm();

        $user = $usersRepository->findOneByEmail($email);
        if (!$user) {
            throw new \DomainException('User with such email is not registered');
        }
        if (!$user->isActivated()) {
            throw new \DomainException('User is not activated');
        }
        if (!$user->isPasswordValid($password)) {
            throw new \DomainException('Password is wrong');
        }
        $session = new Session($user);

        $entityManager->persist($session);
        $entityManager->flush();

        return $session;
    }

    public function changeUserPassword($sessionId, $currentPassword, $newPassword, $newPasswordRepeated) // TODO: rename to changeCurrentUserPassword
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        $entityManager = \ServiceLocator::getEm();

        $session = $sessionsRepository->getValid($sessionId);
        $currentUser = $session->getUser();
        if (!$currentUser->isPasswordValid($currentPassword)) {
            throw new \DomainException('Entered current password is not valid');
        }
        if ($newPassword != $newPasswordRepeated) {
            throw new \DomainException('Passwords are not equal');
        }
        $currentUser->setPassword($newPassword);

        $entityManager->persist($currentUser);
        $entityManager->flush();
    }

    public function logoutUser($sessionId)
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        $entityManager = \ServiceLocator::getEm();

        $session = $sessionsRepository->getValid($sessionId);

        $entityManager->remove($session);
        $entityManager->flush();
    }

    public function viewSession($sessionId)
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        $entityManager = \ServiceLocator::getEm();

        $session = $sessionsRepository->getValid($sessionId);

        $entityManager->flush();
        return $session;
    }

    // TODO: an out of domain scenario for cleaning session tables to support design infrastructure
    // extend config.cli for now, I guess
}