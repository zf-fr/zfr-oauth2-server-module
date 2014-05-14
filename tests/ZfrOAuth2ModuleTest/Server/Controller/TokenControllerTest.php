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

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use ZfrOAuth2\Server\Entity\AccessToken;
use ZfrOAuth2Module\Server\Controller\TokenController;
use ZfrOAuth2Module\Server\Event\TokenEvent;

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
        $authorizationServer = $this->getMock('ZfrOAuth2\Server\AuthorizationServer', [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request = $this->getMock('Zend\Stdlib\RequestInterface');

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->never())->method('handleTokenRequest');

        $this->assertNull($controller->tokenAction($request));
    }

    public function testDelegateToAuthorizationServerIfHttpRequest()
    {
        $authorizationServer = $this->getMock('ZfrOAuth2\Server\AuthorizationServer', [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request  = new HttpRequest();
        $response = new HttpResponse();

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->once())
                            ->method('handleTokenRequest')
                            ->with($request)
                            ->will($this->returnValue($response));

        $this->assertSame($response, $controller->tokenAction($request));
    }

    public function testTriggerEventIfTokenIsCreated()
    {
        $authorizationServer = $this->getMock('ZfrOAuth2\Server\AuthorizationServer', [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request  = new HttpRequest();
        $response = new HttpResponse();

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->once())
                            ->method('handleTokenRequest')
                            ->with($request)
                            ->will($this->returnValue($response));

        $accessToken = new AccessToken();

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $controller->setEventManager($eventManager);

        $response->setStatusCode(200);
        $response->setMetadata('accessToken', $accessToken);

        $eventManager->expects($this->once())
                     ->method('trigger')
                     ->with(TokenEvent::EVENT_TOKEN_CREATED, $this->callback(
                    function(TokenEvent $event) use ($request, $response, $accessToken) {
                        $this->assertSame($request, $event->getRequest());
                        $this->assertSame($response, $event->getResponse());
                        $this->assertSame($accessToken, $event->getAccessToken());

                        return true;
                    }));

        $controller->tokenAction($request);
    }

    public function testTriggerEventIfTokenIsNotCreated()
    {
        $authorizationServer = $this->getMock('ZfrOAuth2\Server\AuthorizationServer', [], [], '', false);
        $controller          = new TokenController($authorizationServer);

        $request  = new HttpRequest();
        $response = new HttpResponse();

        $reflProperty = new \ReflectionProperty($controller, 'request');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($controller, $request);

        $authorizationServer->expects($this->once())
            ->method('handleTokenRequest')
            ->with($request)
            ->will($this->returnValue($response));

        $accessToken = new AccessToken();

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $controller->setEventManager($eventManager);

        $response->setStatusCode(400);

        $eventManager->expects($this->once())
                     ->method('trigger')
                     ->with(TokenEvent::EVENT_TOKEN_FAILED, $this->callback(
                function(TokenEvent $event) use ($request, $response) {
                    $this->assertSame($request, $event->getRequest());
                    $this->assertSame($response, $event->getResponse());
                    $this->assertNull($event->getAccessToken());

                    return true;
                }));

        $controller->tokenAction($request);
    }
}
