BasicCrm project
================

BasicCrm is a demo project for the series of articles on applying Domain-Driven Design (http://en.wikipedia.org/wiki/Domain-driven_design) in PHP.
Working version of the system may be found here: http://BasicCrm.lcf.name
Model designing and building process is desribed in details here: http://blog.lcf.name/search/label/basic%20crm, starting with this article: http://blog.lcf.name/2011/05/application-overview.html

For domain model description see DOMAIN.md

Running with Docker
===================

There is no image distributed for the project at the moment. Clone the repository and build the image with `docker build .`.
Run the application with `docker run -d -p 80:80 ceb93a463e2d`. User your new image id. Access the website via http://127.0.0.1/

The container comes with the web server and the database. By default emails are put in /tmp directory inside the container. In order to overwrite some of the configuration parameters, add `config.local.ini` to /var/www/application/configs/
