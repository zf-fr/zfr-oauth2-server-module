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

namespace ZfrOAuth2ModuleTest\Server\Authentication\Storage;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use ZfrOAuth2\Server\Entity\AccessToken;
use ZfrOAuth2\Server\Entity\TokenOwnerInterface;
use ZfrOAuth2\Server\ResourceServer;
use ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage
 */
class AccessTokenStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ZfrOAuth2\Server\ResourceServer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceServer;

    /**
     * @var AccessTokenStorage
     */
    private $storage;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resourceServer = $this->getMock(ResourceServer::class, [], [], '', false);
        $this->storage        = new AccessTokenStorage($this->resourceServer);
    }

    public function testIsConsideredAsEmptyIfNoAccessToken()
    {
        $this->resourceServer
            ->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with($this->isInstanceOf(ServerRequestInterface::class))
            ->will($this->returnValue(null));

        $this->assertTrue($this->storage->isEmpty());
        $this->assertNull($this->storage->read());
    }

    public function testReadOwnerFromAccessToken()
    {
        $token = new AccessToken();
        $owner = $this->getMock(TokenOwnerInterface::class);

        $token->setOwner($owner);

        $this->resourceServer
            ->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with($this->isInstanceOf(ServerRequestInterface::class))
            ->will($this->returnValue($token));

        $this->assertFalse($this->storage->isEmpty());
        $this->assertSame($owner, $this->storage->read());
    }
}
