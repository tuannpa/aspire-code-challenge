# Aspire mini project

**Prerequisites**

- Install php 8 or above, composer 2.0.
- Install GIT.
- Install docker, docker-compose.

---------------
**Project setup**

1. Run composer install to fetch all necessary Laravel dependencies. Add **--ignore-platform-reqs** flag to avoid version mismatch between packages.

<code>composer install --ignore-platform-reqs</code>

2. Initialize docker containers using Laravel Sail:

<code>./vendor/bin/sail up</code>

- If you wish to configure a Bash alias that allows you to execute Sail's commands more easily, run the following command:
  
<code>alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'</code>

- Then you'll be able to use Sail with:

<code>sail up -d</code>

3. Once all containers are up and running (might take several minutes to install necessary dependencies of the project)


**Phpmyadmin**

