# Self-hosting

Self-hosting is the option for companies and individuals to host an instance 
of Koalati on their server.

A template for self-hosting, along with self-hosting instructions, can be found
on [Koalati's `hosting` GitHub repository](https://github.com/koalatiapp/hosting).


## Self-hosting mode

Self-hosting mode can be toggled by setting the `SELF_HOSTING` environment
variable to `1` (enabled) or `0` (disabled). 

In self-hosting mode, there are no subscriptions: everyone has the same level 
of privilege (equivalent to the highest plan available on the hosted version).


## Invitation-only mode

When self-hosting mode is enabled, you may also enable the invitation only mode.

When enabled, account creation will be disabled unless you are invited into an
organization by an existing user. 

This mode can be toggled via the `INVITE_ONLY_REGISTRATION_MODE` environment 
variable. It defaults to `1` (enabled).

This mode is enabled by default as a security measure, to prevent people 
discovering your internal Koalati instance and abusing it. If your internal 
Koalati instance is protected via some other security measure (VPN, .htpasswd, 
etc.), you may disable this mode.


## Self-hosting specific changes

### File storage
In self-hosting mode, file storage is handled locally instead of relying on an
external storage service (ex.: S3).

Files are stored and served from the `public/storage` directory, which means 
they are also accessible publically. 

## Project screenshots
In self-hosting mode, project thumbnails use the site's `og:image` instead of a 
screenshot of the homepage.

This change is done to simplify the installation and maintenance of self-hosted
Koalati environments.

## Useful commands

- The [`CreateUserCommand`](/src/Command/Security/CreateUserCommand.php) can be 
  used to create a user via the command line. 
