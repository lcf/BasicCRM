BasicCrm project
================

BasicCrm is a demo project for the series of articles on applying Development Driven Design in PHP.
Working version of the system may be found here: http://BasicCrm.lcf.name
Model designing and building process is desribed in details here: http://blog.lcf.name, starting with this article: http://blog.lcf.name/2011/05/application-overview.html


Domain Model
============
    Here you'll find a short description and requirements for every part of the Domain being reflected into the model


Register a company
------------------
1. finds the subscription plan by its identifier in the data storage
2. error if the plan is for some reason not found
3. error if two passwords provided are not equal
4. creates new user admin account based on the email, name and password provided
5. creates company based on company name provided, new admin user and the subscription plan found
6. saves the new company in the data storage

User
----
* has a unique identifier for reference
* has a name
* has a valid email
* has a password not shorter than 6 characters, hashed
* may be either admin or not admin (admin has some special privileges)
* is not admin by default
* there is a way to define whether a user is an admin or not
* belongs to a single company

Company
-------
* has a unique identifier for reference
* has a not empty name
* has an associated subscription plan
* has at least one user and that user must be an administrator
* may be either activated or not activated
* is not activated by default
* has a collection of users belonging to it

Subscription plan
-----------------
* has a unique identifier for reference
* has a name