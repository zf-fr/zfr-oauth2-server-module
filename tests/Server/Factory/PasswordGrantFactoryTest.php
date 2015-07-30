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

namespace ZfrOAuth2ModuleTest\Server\Factory;

use Zend\ServiceManager\ServiceManager;
use ZfrOAuth2Module\Server\Factory\PasswordGrantFactory;
use ZfrOAuth2Module\Server\Factory\RefreshTokenGrantFactory;
use ZfrOAuth2Module\Server\Options\ModuleOptions;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers ZfrOAuth2Module\Server\Factory\PasswordGrantFactory
 */
class PasswordGrantFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateFromFactory()
    {
        $serviceManager = new ServiceManager();

        $pluginManager = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $pluginManager->expects($this->once())->method('getServiceLocator')->will($this->returnValue($serviceManager));

        $serviceManager->setService(
            'ZfrOAuth2Module\Server\Options\ModuleOptions',
            new ModuleOptions([
                'owner_callable' => 'MyOwnerCallable'
            ])
        );

        $serviceManager->setService('MyOwnerCallable', function() {});

        $serviceManager->setService(
            'ZfrOAuth2\Server\Service\AccessTokenService',
            $this->getMock('ZfrOAuth2\Server\Service\TokenService', [], [], '', false)
        );

        $serviceManager->setService(
            'ZfrOAuth2\Server\Service\RefreshTokenService',
            $this->getMock('ZfrOAuth2\Server\Service\TokenService', [], [], '', false)
        );

        $factory = new PasswordGrantFactory();
        $service = $factory->createService($pluginManager);

        $this->assertInstanceOf('ZfrOAuth2\Server\Grant\PasswordGrant', $service);
    }
}
