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

use Zend\Http\Request as HttpRequest;
use Zend\ServiceManager\ServiceManager;
use ZfrOAuth2Module\Server\Factory\AccessTokenStorageFactory;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers ZfrOAuth2Module\Server\Factory\AccessTokenStorageFactory
 */
class AccessTokenStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateFromFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            'ZfrOAuth2\Server\ResourceServer',
            $this->getMock('ZfrOAuth2\Server\ResourceServer', [], [], '', false)
        );

        $application = $this->getMock('Zend\Mvc\ApplicationInterface');
        $application->expects($this->once())->method('getRequest')->will($this->returnValue(new HttpRequest()));
        $serviceManager->setService('Application', $application);

        $factory = new AccessTokenStorageFactory();
        $service = $factory->createService($serviceManager);

        $this->assertInstanceOf('ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage', $service);
    }
}
