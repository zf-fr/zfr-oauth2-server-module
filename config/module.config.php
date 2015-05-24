<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use ZfrOAuth2\Server\AuthorizationServer;
use ZfrOAuth2\Server\ResourceServer;
use ZfrOAuth2\Server\Service\ClientService;
use ZfrOAuth2\Server\Service\ScopeService;
use ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage;
use ZfrOAuth2Module\Server\Controller\AuthorizationController;
use ZfrOAuth2Module\Server\Controller\TokenController;
use ZfrOAuth2Module\Server\Factory\AccessTokenServiceFactory;
use ZfrOAuth2Module\Server\Factory\AccessTokenStorageFactory;
use ZfrOAuth2Module\Server\Factory\AuthorizationCodeServiceFactory;
use ZfrOAuth2Module\Server\Factory\AuthorizationControllerFactory;
use ZfrOAuth2Module\Server\Factory\AuthorizationServerFactory;
use ZfrOAuth2Module\Server\Factory\ClientServiceFactory;
use ZfrOAuth2Module\Server\Factory\GrantPluginManagerFactory;
use ZfrOAuth2Module\Server\Factory\ModuleOptionsFactory;
use ZfrOAuth2Module\Server\Factory\RefreshTokenServiceFactory;
use ZfrOAuth2Module\Server\Factory\ResourceServerFactory;
use ZfrOAuth2Module\Server\Factory\ScopeServiceFactory;
use ZfrOAuth2Module\Server\Factory\TokenControllerFactory;
use ZfrOAuth2Module\Server\Grant\GrantPluginManager;
use ZfrOAuth2Module\Server\Options\ModuleOptions;

return [
    'service_manager' => [
        'factories' => [
            /**
             * Factories that map to a class
             */
            AuthorizationServer::class  => AuthorizationServerFactory::class,
            ResourceServer::class       => ResourceServerFactory::class,
            ClientService::class        => ClientServiceFactory::class,
            ScopeService::class         => ScopeServiceFactory::class,
            AccessTokenStorage::class   => AccessTokenStorageFactory::class,
            ModuleOptions::class        => ModuleOptionsFactory::class,
            GrantPluginManager::class   => GrantPluginManagerFactory::class,

            /**
             * Factories that do not map to a class
             */
            'ZfrOAuth2\Server\Service\AuthorizationCodeService' => AuthorizationCodeServiceFactory::class,
            'ZfrOAuth2\Server\Service\AccessTokenService'       => AccessTokenServiceFactory::class,
            'ZfrOAuth2\Server\Service\RefreshTokenService'      => RefreshTokenServiceFactory::class,
        ]
    ],

    'doctrine' => [
        'driver' => [
            'zfr_oauth2_driver' => [
                'class' => XmlDriver::class,
                'paths' => __DIR__ . '/../../zfr-oauth2-server/config/doctrine',
            ],
            'orm_default' => [
                'drivers' => [
                    'ZfrOAuth2\Server\Entity' => 'zfr_oauth2_driver',
                ],
            ],
        ],

        'configuration' => [
            'orm_default' => [
                'second_level_cache' => [
                    'enabled' => true,

                    'regions' => [
                        'oauth_token_region' => [
                            'lifetime' => 3600
                        ],

                        'oauth_scope_region' => [
                            'lifetime' => 300
                        ]
                    ]
                ]
            ]
        ]
    ],

    'router' => [
        'routes' => [
            'zfr-oauth2-server' => [
                'type'    => 'Literal',
                'options' => [
                    'route' => '/oauth'
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'authorize' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/authorize',
                            'defaults' => [
                                'controller' => AuthorizationController::class,
                                'action'     => 'authorize'
                            ]
                        ]
                    ],

                    'token' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/token',
                            'defaults' => [
                                'controller' => TokenController::class,
                                'action'     => 'token'
                            ]
                        ]
                    ],

                    'revoke' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/revoke',
                            'defaults' => [
                                'controller' => TokenController::class,
                                'action'     => 'revoke'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],

    'console' => [
        'router' => [
            'routes' => [
                'delete-expired-tokens' => [
                    'type'    => 'Simple',
                    'options' => [
                        'route'    => 'oauth2 server delete expired tokens',
                        'defaults' => [
                            'controller' => TokenController::class,
                            'action'     => 'delete-expired-tokens'
                        ]
                    ]
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            AuthorizationController::class => AuthorizationControllerFactory::class,
            TokenController::class         => TokenControllerFactory::class
        ]
    ],

    'zfr_oauth2_server' => [
        'grant_manager' => []
    ]
];
