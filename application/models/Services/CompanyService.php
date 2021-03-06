<?php
namespace Services;

use Domain\Company,
    Domain\User;

class CompanyService
{
    public function confirmCompanyRegistration($companyId, $confirmationCode)
    {
        // Getting objects we're going to need in this service, using our ServiceLocator
        $entityManager = \ServiceLocator::getEm();
        $companiesRepository = \ServiceLocator::getCompaniesRepository();
        $salt = \ServiceLocator::getDomainConfig()->get('confirmationCodeSalt');

        // 1. finds a company by its identifier
        $company = $companiesRepository->find($companyId);
        // 2. error if company isn't found
        if (!$company) {
            throw new \DomainException('Company is not found');
        }
        // 3. activates company with the confirmation code given
        $company->activate($confirmationCode, $salt);
        // 4. persists changes in the data storage
        $entityManager->persist($company);
        $entityManager->flush();
    }

    public function registerCompany($subscriptionId, $companyName, $adminName, $adminEmail,
        $adminPassword, $adminPasswordRepeated)
    {
        // Getting objects we're going to need in this service, using our ServiceLocator
        $subscriptionRepository = \ServiceLocator::getSubscriptionsRepository();
        $entityManager = \ServiceLocator::getEm();
        $usersRepository = \ServiceLocator::getUsersRepository();
        $mailer = \ServiceLocator::getMailer();

        // 1. finds the subscription plan by its identifier in the data storage
        /* @var Domain\Subscription $subscription */
        $subscription = $subscriptionRepository->find($subscriptionId);
        // 2. error if the plan is for some reason not found
        if (!$subscription) {
            throw new \DomainException('Subscription is not found');
        }
        // 3. error if two passwords provided are not equal
        if ($adminPassword != $adminPasswordRepeated) {
            throw new \DomainException('Passwords are not equal');
        }
        // 4. error if email is already registered in the system
        if ($usersRepository->findByEmail($adminEmail)) {
            throw new \DomainException('User with email ' . $adminEmail . ' has been already registered');
        }
        // 5. creates new user admin account based on the email, name and password provided
        $adminUser = new User($adminEmail, $adminName, $adminPassword, true);
        // 6. creates company based on company name provided, new admin user and the subscription plan found
        $company = new Company($companyName, $subscription, $adminUser);
        $entityManager->transactional(function($entityManager) use ($company, $mailer) {
            // 7. saves the new company in the data storage
            $entityManager->persist($company);
            $entityManager->flush();
            // 8. sends out a confirmation email to confirm the email address
            $mailer->registrationConfirmation($company);
        });
    }

    public function switchAdmin($sessionId, $currentAdminPassword, $newAdminId)
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        $entityManager = \ServiceLocator::getEm();
        $session = $sessionsRepository->getValid($sessionId);
        $currentUser = $session->getUser();
        if (!$currentUser->isAdmin()) {
            throw new \DomainException('Only admin can change administrator');
        }
        if (!$currentUser->isPasswordValid($currentAdminPassword)) {
            throw new \DomainException('Password is wrong');
        }
        $company = $currentUser->getCompany();
        $company->switchAdminTo($newAdminId);
        $entityManager->persist($company);
        $entityManager->flush();
    }

    public function addUserToCompany($sessionId, $userName, $userEmail)
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        $entityManager = \ServiceLocator::getEm();
        $usersRepository = \ServiceLocator::getUsersRepository();
        $mailer = \ServiceLocator::getMailer();

        $session = $sessionsRepository->getValid($sessionId);
        if (!$session->getUser()->isAdmin()) {
            throw new \DomainException('Only admin can add new users');
        }
        if ($usersRepository->findByEmail($userEmail)) {
            throw new \DomainException('User with email ' . $userEmail . ' has been already registered'); // TODO: trsl
        }
        $password = substr(md5(rand(1, 1000000)), 0, 8);
        $user = new User($userEmail, $userName, $password);
        $session->getUser()->getCompany()->addUser($user);

        $entityManager->transactional(function($entityManager) use ($user, $mailer, $password) {
            $entityManager->persist($user); // TODO: file a bug to PhpStorm issue tracker about not seeing vars types here
            $entityManager->flush();

            $mailer->newUserWelcome($user, $password);
        });
    }

    public function listCompanyUsers($sessionId)
    {
        $sessionsRepository = \ServiceLocator::getSessionsRepository();
        return $sessionsRepository->getValid($sessionId)
                                  ->getUser()
                                  ->getCompany()
                                  ->getUsers();
    }
}