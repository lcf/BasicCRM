<?php
namespace Services;

use Domain\Company,
    Domain\User;

class CompanyService
{
    public function registerCompany($subscriptionId, $companyName, $adminName, $adminEmail,
        $adminPassword, $adminPasswordRepeated)
    {
        // Getting objects we're going to need in this service, using our ServiceLocator
        $subscriptionRepository = ServiceLocator::getSubscriptionsRepository();
        $entityManager = ServiceLocator::getEntityManager();

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
        // 4. creates new user admin account based on the email, name and password provided
        $adminUser = new User($adminEmail, $adminName, $adminPassword, true);
        // 5. creates company based on company name provided, new admin user and the subscription plan found
        $company = new Company($companyName, $subscription, $adminUser);
        // 6. saves the new company in the data storage
        $entityManager->persist($company);
        $entityManager->flush();
    }
}