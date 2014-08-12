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

return [
    'service_manager' => [
        'factories' => [
            /**
             * Factories that map to a class
             */
            'ZfrOAuth2\Server\AuthorizationServer'                             => 'ZfrOAuth2Module\Server\Factory\AuthorizationServerFactory',
            'ZfrOAuth2\Server\ResourceServer'                                  => 'ZfrOAuth2Module\Server\Factory\ResourceServerFactory',
            'ZfrOAuth2\Server\Service\ClientService'                           => 'ZfrOAuth2Module\Server\Factory\ClientServiceFactory',
            'ZfrOAuth2\Server\Service\ScopeService'                            => 'ZfrOAuth2Module\Server\Factory\ScopeServiceFactory',
            'ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage' => 'ZfrOAuth2Module\Server\Factory\AccessTokenStorageFactory',
            'ZfrOAuth2Module\Server\Options\ModuleOptions'                     => 'ZfrOAuth2Module\Server\Factory\ModuleOptionsFactory',
            'ZfrOAuth2Module\Server\Grant\GrantPluginManager'                  => 'ZfrOAuth2Module\Server\Factory\GrantPluginManagerFactory',

            /**
             * Factories that do not map to a class
             */
            'ZfrOAuth2\Server\Service\AuthorizationCodeService' => 'ZfrOAuth2Module\Server\Factory\AuthorizationCodeServiceFactory',
            'ZfrOAuth2\Server\Service\AccessTokenService'       => 'ZfrOAuth2Module\Server\Factory\AccessTokenServiceFactory',
            'ZfrOAuth2\Server\Service\RefreshTokenService'      => 'ZfrOAuth2Module\Server\Factory\RefreshTokenServiceFactory',
        ]
    ],

    'doctrine' => [
        'driver' => [
            'zfr_oauth2_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/../../zfr-oauth2-server/config/doctrine',
            ],
            'orm_default' => [
                'drivers' => [
                    'ZfrOAuth2\Server\Entity' => 'zfr_oauth2_driver',
                ],
            ],
        ],
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
                                'controller' => 'ZfrOAuth2Module\Server\Controller\AuthorizationController',
                                'action'     => 'authorize'
                            ]
                        ]
                    ],

                    'token' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/token',
                            'defaults' => [
                                'controller' => 'ZfrOAuth2Module\Server\Controller\TokenController',
                                'action'     => 'token'
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
                            'controller' => 'ZfrOAuth2Module\Server\Controller\TokenController',
                            'action'     => 'delete-expired-tokens'
                        ]
                    ]
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            'ZfrOAuth2Module\Server\Controller\AuthorizationController' => 'ZfrOAuth2Module\Server\Factory\AuthorizationControllerFactory',
            'ZfrOAuth2Module\Server\Controller\TokenController'         => 'ZfrOAuth2Module\Server\Factory\TokenControllerFactory'
        ]
    ],

    'zfr_oauth2_server' => [
        'grant_manager' => []
    ]
];
