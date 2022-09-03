# Symfony REST Api Sample with JWT and AWS S3

This is a sample REST Api application made with the Symfony 5.4 and JWT implementation and uploading files to AWS S3.

## Requirements

PHP 7.2.5 or higher (PHP 7.3 recommended)

Composer 2.*

## Setup

First clone this repository.

```
git clone git@github.com:alidevweb/symfony-rest-api-jwt-aws.git symfony_rest_api_jwt_aws
```

Install the dependencies.

```
cd symfony_rest_api_jwt_aws
composer install
```

Setup your .env.local file to keep your access and secrets private.

```
cp .env .env.local
```


Open the `.env.local` file and make any adjustments you need - specifically
`DATABASE_URL`.

**Setup the Database**

Again, make sure `.env.local` is setup for your computer. Then, create the database & tables!

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If you get an error that the database exists, that should
be ok. But if you have problems, completely drop the
database (`doctrine:database:drop --force`) and try again.

**Start the built-in web server**

You can use Nginx or Apache, but Symfony's local web server
works even better.

To install the Symfony local web server, follow
"Downloading the Symfony client" instructions found
here: https://symfony.com/download - you only need to do this
once on your system.

Then, to start the web server, open a terminal, move into the project, and run:

```
symfony serve
```

Now check out the site at `https://127.0.0.1:8000`

Have fun!

