# Web API

Koalati offers a REST API, which is built with [API Platform](https://api-platform.com/).

Most of the implementation can be understood by reading API Platform's 
documentation. This document describes the layers that are applied on top of 
API Platform for Koalati's specific needs.

## Authentication

The API's authentication uses JWT as bearer tokens. This is based on 
[lexik/jwt-authentication-bundle](https://github.com/lexik/LexikJWTAuthenticationBundle).

Refresh tokens are made available by [gesdinet/jwt-refresh-token-bundle](https://github.com/markitosgv/JWTRefreshTokenBundle).

## Encrypted IDs

IDs are always encrypted in serialized data within the application. A few 
things had to be put in place to make the API Platform work with these 
encrypted IDs:

- `App\Api\Routing\EncryptedIriConverter` decorates the default `api_platform.iri_converter`
  to automatically encrypt & decrypt resource identifiers in IRIs.
- `App\Api\State\EncryptedIdsCallableProvider` decorates the default `api_platform.state_provider.locator`
  to decrypt IDs when fetching entities (and then lets the default entity 
	providers do their job as usual).

## Security

### API access control

Security checks to make sure the user has access to the API takes place at the
beginning of every API request within the `App\Api\Security\AuthenticationJwtListener`.

### Query filters (collections)

Query filters are used for all entities that have collection endpoints, to 
ensure the user has access to the entities that are returned.

These filters implement the `QueryCollectionExtensionInterface` interface and 
can be found in the `App\Api\Security` namespace.

### Voters

For all operations other than fetching collections of entities, [voters](https://symfony.com/doc/current/security/voters.html) 
are used to check if users have the necessary privileges to go access the 
target entity and/or to perform the desired action.

## Invalid filter exception

By default, the API Platform simply ignores invalid filter values coming from
user requests.

To provide better feedback and help the developers who use the API to improve
their implementation, the `App\Api\Log\InvalidFilterLogHandler` listens for 
logs from the API Platform mentioning invalid filter values and throws a Bad 
Request error with a messaged detailing which filter value is invalid.

Ex.: `Invalid filter value for field author`.
