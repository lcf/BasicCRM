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
}