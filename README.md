# ZfrOAuth2Module\Server

[![Build Status](https://travis-ci.org/zf-fr/zfr-oauth2-server-module.png?branch=master)](https://travis-ci.org/zf-fr/zfr-oauth2-server-module)
[![Latest Stable Version](https://poser.pugx.org/zfr/zfr-oauth2-server-module/v/stable.png)](https://packagist.org/packages/zfr/zfr-oauth2-server-module)
[![Coverage Status](https://coveralls.io/repos/zf-fr/zfr-oauth2-server-module/badge.png)](https://coveralls.io/r/zf-fr/zfr-oauth2-server-module)
[![Total Downloads](https://poser.pugx.org/zfr/zfr-oauth2-server-module/downloads.png)](https://packagist.org/packages/zfr/zfr-oauth2-server-module)

ZfrOAuth2Module\Server is a Zend Framework 2 module for ZfrOAuth2\Server. Its goal is to easily create a OAuth 2
compliant server.

## Requirements

- PHP 5.4 or higher
- [ZfrOAuth2Server](https://github.com/zf-fr/zfr-oauth2-server)

## Versioning note

Please note that until I reach 1.0, I **WILL NOT** follow semantic version. This means that BC can occur between
0.1.x and 0.2.x releases. If you are using this in production, please set your dependency using 0.1.*, for instance.

## Installation

Installation is only officially supported using Composer:

```sh
php composer.phar require zfr/zfr-oauth2-server-module:0.2.*
```

Copy-paste the `zfr_oauth2_server.global.php.dist` file to your `autoload` folder, and enable the module by adding
`ZfrOAuth2Module\Server` to your `application.config.php` file.

## Documentation

### Configuring the module

ZfrOAuth2Module\Server provides a lot of default configuration. However, there are some information you need to provide.

#### Setting the User class

When a token is generated, it is automatically linked to an owner. Most of the time, it will be a user. For this
mapping to work, you must make sure your user class implements the `ZfrOAuth2\Server\Entity\TokenOwnerInterface`
interface. Then, you need to modify the Doctrine mapping to associate this interface with your own user
class. The code is already set in the `zfr_oauth2_server.global.php.dist` file:

```php
return [
    'doctrine' => [
        'entity_resolver' => [
            'orm_default' => [
                'ZfrOAuth2\Server\Entity\TokenOwnerInterface' => 'Application\Entity\User'
            ]
        ]
    ]
]
```

#### Adding grant types

By default, your OAuth2 server does not support anything. You must configure it by adding all the grants you
want to support. For instance, the following config will make your server compatible with the "User credentials"
grant as well as the "Refresh token" grant:

```php
return [
    'zfr_oauth2_server' => [
        'grants' => [
            'ZfrOAuth2\Server\Grant\PasswordGrant',
            'ZfrOauth2\Server\Grant\RefreshTokenGrant'
        ]
    ]
]
```

#### Specifying a callable for validating password and username

When using the "User credentials" grant (also called the Password grant), the username and password are automatically
passed to a callable. If the callable return a `TokenOwnerInterface` instance, then it's considered as valid and
the access token is created. Otherwise, an error is thrown.

```php
return [
    'zfr_oauth2_server' => [
        'owner_callable' => function($username, $password) {
            // If valid, return the user, otherwise return null
        }
    ]
];
```

You can also pass a service key, that will be pulled from the service manager, if you need to inject dependencies.

### Using the event manager

There are a lot of use cases where you would like to execute specific code when a token is created (or when it
could not be created). Such use cases include: log login, modify generic OAuth2 response to include additional fields...

To that extent, ZfrOAuth2 module trigger events in the `TokenController`. Two events are triggered:

* `ZfrOAuth2Module\Server\Event\TokenEvent::EVENT_TOKEN_CREATED`: event that is triggered when the access token has
been properly created and persisted.
* `ZfrOAuth2Module\Server\Event\TokenEvent::EVENT_TOKEN_FAILED`: event that is triggered when an error has occurred (
wrong credentials, missing grant...).

In both cases, the `TokenEvent` event lets you access to the request, the response body and the access token (if available).

Here is an example:

```php
use ZfrOAuth2Module\Server\Event\TokenEvent;

class Module
{
    public function onBootstrap(EventInterface $event)
    {
        /* @var \Zend\Mvc\Application $application */
        $application   = $event->getTarget();
        $eventManager  = $application->getEventManager();
        $sharedManager = $eventManager->getSharedManager();

        $sharedManager->attach(
            'ZfrOAuth2Module\Server\Controller\TokenController',
            TokenEvent::EVENT_TOKEN_CREATED,
            [$this, 'tokenCreated']
        );

        $sharedManager->attach(
            'ZfrOAuth2Module\Server\Controller\TokenController',
            TokenEvent::EVENT_TOKEN_FAILED,
            [$this, 'tokenFailed']
        );
    }

    public function tokenCreated(TokenEvent $event)
    {
        // We can log the access token
        $accessToken = $event->getAccessToken();
        // ...

        // Or we can alter the response body, if we need to
        $body                 = $event->getResponseBody();
        $body['custom_field'] = 'bar';

        // Response will be automatically changed by the controller
    }

    public function tokenFailed(TokenEvent $event)
    {
        // We can inspect the response to know what happen and log the failure
        $body = $event->getResponseBody();
    }
}
```

### Delete expired tokens

ZfrOAuth2Module\Server offers a console route you can use to delete expired access tokens. You can use this as a CRON
task to clean your database. In the `public` folder, use the following command:

`php index.php oauth2 server delete expired tokens`.
