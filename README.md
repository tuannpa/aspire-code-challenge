# Aspire mini project

**Prerequisites**

- Install php 8 or above, composer 2.0.
- Install GIT.
- Install docker, docker-compose.

---------------
**Project setup**

1. Initialize docker containers using Laravel Sail:

<code>./vendor/bin/sail up</code>

- If you wish to configure a Bash alias that allows you to execute Sail's commands more easily, run the following command:
  
<code>alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'</code>

- Then you'll be able to use Sail with:

<code>sail up -d</code>

2. Once all containers are up and running (might take several minutes to install necessary dependencies of the project)


**Phpmyadmin**

