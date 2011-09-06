BasicCrm project
================

BasicCrm is a demo project for the series of articles on applying Domain-Driven Design (http://en.wikipedia.org/wiki/Domain-driven_design) in PHP.
Working version of the system may be found here: http://BasicCrm.lcf.name
Model designing and building process is desribed in details here: http://blog.lcf.name, starting with this article: http://blog.lcf.name/2011/05/application-overview.html


Domain Model
============
    Here you'll find a short description and requirements for every part of the Domain reflected into the model

TODO >>> Add a user // TODO: twitter bootstrap for this too?
----------
TODO >>> 1. gets valid session by its identifier
TODO >>> 2. error if current user is not an admin
TODO >>> 3. error if email provided is already registered in the system
TODO >>> 4. creates new non admin user account based on the email, name and password provided
TODO >>> 5. adds new user account to the current company // TODO: add subsciption restraints
TODO >>> 6. saves the new user in the data storage

TODO >>> Change current user password
----------------------------
TODO >>> 1. gets valid session by its identifier
TODO >>> 2. error if provided current password is not valid
TODO >>> 3. error if new password provided twice is not repeated correctly
TODO >>> 4. changes user password // TODO change User entity to rely on changePassword function in construction to avoid logic duplication
TODO >>> 6. saves user in the data storage

Register a company
------------------
1. finds the subscription plan by its identifier in the data storage
2. error if the plan is for some reason not found
3. error if two passwords provided are not equal
4. error if email is already registered in the system
5. creates new user admin account based on the email, name and password provided
6. creates company based on company name provided, new admin user and the subscription plan found
7. saves the new company in the data storage
8. sends out a confirmation email to confirm the email address

Registration confirmation
-------------------------
1. finds company by its identifier
2. error if company isn't found
3. activates company with the confirmation code given and security salt
4. persists changes in the data storage

User login
----------
1. finds user by email passed
2. error if user is not found
3. error if found user in is not activated
4. checks if password is a valid one
5. error if password is not valid
6. starts new session for the user
7. persists session in the data storage
8. returns new session data

User logout
-----------
1. gets valid session by its identifier
2. removes session from data storage

View session data
-----------------
1. gets valid session by its identifier
2. returns session data

SessionsRepository
-----------------
* there is a way to retrieve a valid session by its id
    1. finds session by its id
    2. error if session isn't found
    3. error if session isn't valid
    4. refreshes session
    5. persists changes
    6. returns session

Session
-------
* has a secure unique identifier, string of 32 random characters, used to refer it
* there is a way to find out a session identifier
* there is a way to find out whether a session is valid or not
    1. session stays valid for one day since it was last used
* has last used time, the time when it was created or used last time.
* there is a way to prolong session being valid.
* is associated with the user who started the session
* there is a way to find out the user who started the session

User
----
* has a unique identifier for reference
* has a name
* has a valid email
* has a password not shorter than 6 characters, hashed
* may be either admin or not admin (admin has some special privileges)
* is not admin by default
* there is a way to define whether a user is an admin or not
* there is a way to find out user email
* there is a way to find out user name
* there is a way to find out whether a user is activated
    1. user is considered activated if the company they're in is activated
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
* there is a way to calculate the code required for company registration confirmation
    1. to calculate the code security salt is required
    2. code is a hash function from company id, security salt and company name
* may be activated with a confirmation code and security salt
    1. error if attempt to activate an already activated company
    2. error if confirmation code is not valid
    3. activates company
* there is a way to figure out whether a company is activated
* there is a way to figure out who's the administrator of a company
* there is a way define a company's unique identifier


Subscription plan
-----------------
* has a unique identifier for reference
* has a name