# Import CSV file and update database application

## Setup

**Download Composer dependencies**

Make sure you have [Composer installed](https://getcomposer.org/download/)
and then run:

```
composer install
```

**Database Setup**

The code comes with a `docker-compose.yaml` file. 
Use Docker to boot a database container. You will still have PHP installed
locally, but you'll connect to a database inside Docker. 

First, make sure you have [Docker installed](https://docs.docker.com/get-docker/)
and running. To start the container, run:

```
docker-compose up -d
```

Next, build the database and execute the migrations with:

```
# "symfony console" is equivalent to "bin/console"
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
```

(If you get an error about "MySQL server has gone away", just wait
a few seconds and try again - the container is probably still booting).

If you do *not* want to use Docker, just make sure to start your own
database server and update the `DATABASE_URL` environment variable in
`.env` or `.env.local` before running the commands above.

**Start the Symfony web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the
project, and run:

```
symfony serve
```
## Authorization

After you enter first page http://127.0.0.1:8000 you will notice you need to enter password.
Correct password is : access
After that it will redirect you to secured page.

## Import file

You can easily import file in csv format. 
Example csv file you can find in Example folder

## Import file

After you properly import file initiate database update using command : `php bin/console app:update-product-list` 