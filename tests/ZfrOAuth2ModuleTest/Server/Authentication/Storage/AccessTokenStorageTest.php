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

use Zend\Authentication\Result;
use Zend\Http\Request as HttpRequest;
use ZfrOAuth2\Server\Entity\AccessToken;
use ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers \ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage
 */
class AccessTokenStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testIsConsideredAsEmptyIfNoAccessToken()
    {
        $resourceServer = $this->getMock('ZfrOAuth2\Server\ResourceServer', [], [], '', false);
        $request        = new HttpRequest();

        $storage = new AccessTokenStorage($resourceServer);
        $storage->setRequest($request);

        $resourceServer->expects($this->once())
                       ->method('getAccessToken')
                       ->with($request)
                       ->will($this->returnValue(null));

        $this->isTrue($storage->isEmpty());
    }

    public function testReadOwnerFromAccessToken()
    {
        $resourceServer = $this->getMock('ZfrOAuth2\Server\ResourceServer', [], [], '', false);
        $request        = new HttpRequest();

        $storage = new AccessTokenStorage($resourceServer);
        $storage->setRequest($request);

        $token = new AccessToken();
        $owner = $this->getMock('ZfrOAuth2\Server\Entity\TokenOwnerInterface');
        $token->setOwner($owner);

        $resourceServer->expects($this->once())
                       ->method('getAccessToken')
                       ->with($request)
                       ->will($this->returnValue($token));

        $this->assertSame($owner, $storage->read());
    }
}
