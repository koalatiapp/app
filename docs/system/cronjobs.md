# Cron jobs

Cron jobs are recurring tasks that must be executed on a predefined schedule.

In this project, each cron job corresponds to a [Symfony Console Command](https://symfony.com/doc/current/console.html).
These commands are located in the `src/Command` directory.

The schedule on which a command must be executed is defined in the `config/cronjobs` file, using the regular cronjob format.  
This file is copied over to the server's crontab configs in `docker/php/docker-entrypoint.sh`.

Each cron job in that file must be described by a comment.  
Ex.:

```
# Send trial ending soon notices via email
0 * * * *	/srv/app/bin/console app:email:trial-ending-notices
```

The outputs of the cronjobs are usually muted, but all PHP errors will be reported to Sentry if the `SENTRY_DSN` environment variable is configured.

## List of all cron jobs

| Command                                     | Description                                | Frequency                  |
|---------------------------------------------|--------------------------------------------|----------------------------|
| N/A (Symfony Messenger)                     | Consume async messages using the Messenger | `* * * * *` (every minute) |
| `App\Command\Email\SendTrialNoticesCommand` | Send trial ending soon notices via email.  | `0 * * * *` (every hour)   |
