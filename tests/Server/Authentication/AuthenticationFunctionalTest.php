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

namespace ZfrOAuth2ModuleTest\Server\Authentication\Adapter;

use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use ZfrOAuth2\Server\Entity\AccessToken;
use ZfrOAuth2\Server\Entity\TokenOwnerInterface;
use ZfrOAuth2\Server\Exception\OAuth2Exception;
use ZfrOAuth2\Server\ResourceServer;
use ZfrOAuth2Module\Server\Authentication\Storage\AccessTokenStorage;

/**
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @licence MIT
 *
 * @coversNothing
 */
class AuthenticationFunctionalTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ZfrOAuth2\Server\ResourceServer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceServer;

    /**
     * @var AccessTokenStorage
     */
    private $authenticationStorage;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resourceServer        = $this->getMock(ResourceServer::class, [], [], '', false);
        $this->authenticationStorage = new AccessTokenStorage($this->resourceServer);
        $this->authenticationService = new AuthenticationService($this->authenticationStorage);
    }

    public function testSuccessAuthenticationOnValidToken()
    {
        $token = new AccessToken();
        $owner = $this->getMock(TokenOwnerInterface::class);
        $token->setOwner($owner);

        $this
            ->resourceServer
            ->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with($this->isInstanceOf(PsrServerRequestInterface::class))
            ->will($this->returnValue($token));


        $this->assertTrue($this->authenticationService->hasIdentity());
        $this->assertSame($owner, $this->authenticationService->getIdentity());
    }

    public function testFailAuthenticationOnNoToken()
    {
        $token = new AccessToken();
        $owner = $this->getMock(TokenOwnerInterface::class);
        $token->setOwner($owner);

        $this
            ->resourceServer
            ->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with($this->isInstanceOf(PsrServerRequestInterface::class))
            ->will($this->returnValue(null));

        $this->assertFalse($this->authenticationService->hasIdentity());
        $this->assertNull($this->authenticationService->getIdentity());
    }

    public function testFailAuthenticationOnExpiredToken()
    {
        $token = new AccessToken();
        $owner = $this->getMock(TokenOwnerInterface::class);
        $token->setOwner($owner);

        $this
            ->resourceServer
            ->expects($this->atLeastOnce())
            ->method('getAccessToken')
            ->with($this->isInstanceOf(PsrServerRequestInterface::class))
            ->will($this->throwException(new OAuth2Exception('Expired token', 123)));

        $this->setExpectedException(OAuth2Exception::class, 'Expired token', 123);

        $this->authenticationService->getIdentity();
    }
}
