# Messenger

[Symfony's Messenger](https://symfony.com/doc/current/messenger.html#consuming-messages-running-the-worker) runs in the `php` container, handled via supervisor.

The configuration for the messenger can be found in:
- [`config/packages/messenger.yaml`](/config/packages/messenger.yaml)
- [`docker/php/supervisor/supervisord.conf`](/docker/php/supervisor/supervisord.conf)
