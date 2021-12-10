# Setting up your environment variables

Environment variables for your environment are contained in `.env.local`. If you don't have an `.env.local` file yet, go ahead and create one.

To get started, look at the contents of `.env`: this template contains all of the default values for the application.
Any environment variable you haven't re-defined in your `.env.local` file will take its value in the `.env` file (if it is defined there).

Here is a detailed list of all the environment variables, why they are needed, and how you should define them.

## Required variables

| Name                      | Purpose                             | How to set it up                                                                                |
|---------------------------|-------------------------------------|-------------------------------------------------------------------------------------------------|
| APP_ENV                   | Symfony configuration               | Usually `prod` or `dev` ([learn more on Symfony's documentation](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment)) |
| APP_SECRET                | Symfony configuration               | Random hash used for CSRF token generation.                                                     |
| MAILER_DSN                | Symfony configuration               | https://symfony.com/doc/current/mailer.html#transport-setup                                     |
| MESSENGER_TRANSPORT_DSN   | Symfony configuration               | https://symfony.com/doc/current/messenger.html#transports-async-queued-messages                 |
| SYMFONY_VERSION           | Docker configuration                | Defines the version of Symfony to use on a fresh install (use the latest stable version)        |
| SERVER_NAME               | Caddy configuration                 | Domain configuration for the web server ([first line of Caddyfile](https://caddyserver.com/docs/quick-starts/https#caddyfile)) |
| DATABASE_URL              | Database (Symfony configuration)    | https://symfony.com/doc/current/doctrine.html#configuring-the-database                          |
| REDIS_HOST                | PHP sessions                        | Hostname for the Redis database                                                                 |
| REDIS_PASSWORD            | PHP sessions                        | Redis password                                                                                  |
| REDIS_PORT                | PHP sessions                        | Redis port                                                                                      |
| KOALATI_RELEASE_VERSION   | Release tracking & asset versioning | Enter the current release version of the app (or a random string for local development)         |
| MERCURE_URL               | Real-time client-server updates     | https://symfony.com/doc/current/mercure.html#configuration (corresponds to `MERCURE_URL`)       |
| MERCURE_JWT_TOKEN         | Real-time client-server updates     | https://symfony.com/doc/current/mercure.html#configuration (corresponds to `MERCURE_JWT_SECRET`)|
| MERCURE_PUBLISHER_JWT     | Real-time client-server updates     | https://mercure.rocks/docs/hub/config#environment-variables                                     |
| MERCURE_SUBSCRIBER_JWT    | Real-time client-server updates     | https://mercure.rocks/docs/hub/config#environment-variables                                     |
| STORAGE_REGION            | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_VERSION           | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_AUTH_KEY          | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_AUTH_SECRET       | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_BUCKET            | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_ENDPOINT          | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| STORAGE_CDN_URL           | Storage of user-generated media     | Configure with any S3-standardized hosting service (Amazon S3, DigitalOcean Spaces, etc.)       |
| TOOLS_API_URL             | Tools service API (recommendations) | Define the URL at which the tools service API is reachable (including the port, if not 80/443)  |
| TOOLS_API_BEARER_TOKEN    | Tools service API (recommendations) | https://github.com/koalatiapp/tools-service#authentication                                      |
| APIFLASH_ACCESS_KEY       | Project screenshot                  | Create an account on [API FLASH](https://apiflash.com/) and enter your access key here.         |
| OPENGRAPHIO_API_KEY       | URL previews                        | Create an account on [OpenGraph.io](https://www.opengraph.io/) and enter your API key here.     |
| URLMETA_ACCOUNT_EMAIL     | URL previews                        | Create an account on [URL Meta](https://urlmeta.org/) and enter your account's email here.      |
| URLMETA_API_KEY           | URL previews                        | Create an account on [URL Meta](https://urlmeta.org/) and enter your API key here.              |

## Production specific

| Name                      | Purpose                             | How to set it up                                                                                |
|---------------------------|-------------------------------------|-------------------------------------------------------------------------------------------------|
| SENTRY_DSN                | Error tracking                      | Create an account on Sentry.io and enter the DSN.                                               |


## Development specific

| Name                      | Purpose                             | How to set it up                                                                                |
|---------------------------|-------------------------------------|-------------------------------------------------------------------------------------------------|
| MYSQL_HOST                | Database (Docker config)            | Hostname for the MySQL database                                                                 |
| MYSQL_USER                | Database (Docker config)            | MySQL username                                                                                  |
| MYSQL_PASSWORD            | Database (Docker config)            | MySQL password                                                                                  |
| MYSQL_DATABASE            | Database (Docker config)            | MySQL database                                                                                  |
| MYSQL_PORT                | Database (Docker config)            | MySQL port                                                                                      |
| MYSQL_VERSION             | Database (Docker config)            | MySQL server version                                                                            |
| ADMINER_PORT              | Database management (Docker config) | Port on which Adminer should run                                                                |
| TOOLS_API_JWT_SECRET      | Tool service configuration          | Corresponds to [JWT_SECRET](https://github.com/koalatiapp/tools-service#environment-variables)  |
| TOOLS_API_AUTH_ACCESS_TOKEN | Tool service configuration        | Corresponds to [AUTH_ACCESS_TOKEN](https://github.com/koalatiapp/tools-service#environment-variables) |
| TOOLS_API_PGUSER          | Tool service configuration          | Corresponds to [PGUSER](https://github.com/koalatiapp/tools-service#environment-variables)      |
| TOOLS_API_PGPASSWORD      | Tool service configuration          | Corresponds to [PGPASSWORD](https://github.com/koalatiapp/tools-service#environment-variables)  |
| TOOLS_API_PGDATABASE      | Tool service configuration          | Corresponds to [PGDATABASE](https://github.com/koalatiapp/tools-service#environment-variables)  |
| TOOLS_API_PGVERSION       | Tool service configuration          | Corresponds to [PGVERSION](https://github.com/koalatiapp/tools-service#environment-variables)   |
