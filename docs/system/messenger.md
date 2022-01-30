# Messenger

[Symfony's Messenger](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker) runs automatically in the background of the `php` container.

This is currently done via a [cron job](cronjobs.md]).

The outputs of the command are muted by default, but all PHP errors are reported to Sentry if the `SENTRY_DSN` environment variable is configured.
