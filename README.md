BasicCrm project
================

BasicCrm is a demo project for the series of articles on applying Domain-Driven Design (http://en.wikipedia.org/wiki/Domain-driven_design) in PHP.
Working version of the system may be found here: http://BasicCrm.lcf.name
Model designing and building process is desribed in details here: http://blog.lcf.name/search/label/basic%20crm, starting with this article: http://blog.lcf.name/2011/05/application-overview.html


Domain Model
============
    Here you'll find a short description and requirements for every part of the Domain reflected into the model

Switch company administrator
------------
1. gets valid session by its identifier
2. error if current user is not an admin
3. error if password specified is not a valid one
4. switches admin for the current user's company to the user referenced by id
5. saves changed company

Add a user to a company
----------
1. gets valid session by its identifier
2. error if current user is not an admin
3. error if email provided is already registered in the system
4. generates new random password of length 8 for the new user
5. creates new non admin user account based on the email and name provided, password generated
6. adds new user account to the current company
7. saves the new user in the data storage
8. sends an email for the new user with their login and password

Change current user password
------------------------------------
1. gets valid session by its identifier
2. gets current user from the session
3. error if provided current user's password is not valid
4. error if new password provided twice is not repeated correctly
5. changes current user's password
6. saves the user in the data storage

Register a company
------------------
1. finds the subscription plan by its identifier in the data storage
2. error if the plan is for some reason not found
3. error if two passwords provided are not equal
4. error if email is already registered in the system
5. creates new user admin account based on the email, name and password provided
6. creates company based on company name provided, new admin user and the subscription plan found
7. saves the new company in the data storage
8. sends a confirmation email to confirm the email address

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
* has a password set on creation
* may be either admin or not admin (admin has some special privileges)
* is not admin by default
* there is a way to define whether a user is an admin or not
* there is a way to find out user email
* there is a way to find out user unique identifier
* there is a way to find out user name
* there is a way to find out whether a user is activated
    1. user is considered activated if the company they're in is activated
* there is a way to set a password
    1. not shorter than 6 characters, hashed
* belongs to a single company
* there is a way to revoke admin rights from a user
    1. error if the user is not an admin
* there is a way to grant a user administrative permissions
    1. error if the user is already an admin

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
* there is a way to add a user to a company
    1. error if user is admin
    2. error if users limit for the company subscription is reached
    3. associates user with the company
    4. adds user to the collection of users belonging to it
* there is a way to switch administrator for the company to a user referenced by id
    1. error if the user referenced by id is not a member of the company
    2. removes admin rights from the current user
    3. grants admin privileges to the new user
* there is a way to get all company's users

Subscription plan
-----------------
* has a unique identifier for reference
* has a name
* has a certain number of users allowed
* has a certain number of clients allowed
* there is a way to get users limit
* there is a way to get clients limit