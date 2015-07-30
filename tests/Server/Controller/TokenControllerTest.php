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

namespace ZfrOAuth2ModuleTest\Server\Controller;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Zend\Diactoros\Response as PsrResponse;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Stdlib\RequestInterface;
use ZfrOAuth2\Server\AuthorizationServer;
use ZfrOAuth2Module\Server\Controller\TokenController;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers ZfrOAuth2Module\Server\Controller\TokenController
 */
class TokenControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testDoNothingIfNotHttpRequest()
    {
        $authorizationServer = $this->getMock(AuthorizationServer::class, [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request = $this->getMock(RequestInterface::class);

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->never())->method('handleTokenRequest');

        $this->assertNull($controller->tokenAction($request));
    }

    public function testDelegateToAuthorizationServerIfHttpRequest()
    {
        $authorizationServer = $this->getMock(AuthorizationServer::class, [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request  = new HttpRequest();
        $response = new PsrResponse();

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->once())
                            ->method('handleTokenRequest')
                            ->with($this->isInstanceOf(PsrRequestInterface::class))
                            ->will($this->returnValue($response));

        $this->assertInstanceOf(HttpResponse::class, $controller->tokenAction($request));
    }

    public function testCanRevokeToken()
    {
        $authorizationServer = $this->getMock(AuthorizationServer::class, [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request  = new HttpRequest();
        $response = new PsrResponse();

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->once())
                            ->method('handleRevocationRequest')
                            ->with($this->isInstanceOf(PsrRequestInterface::class))
                            ->will($this->returnValue($response));

        $this->assertInstanceOf(HttpResponse::class, $controller->revokeAction($request));
    }
}
