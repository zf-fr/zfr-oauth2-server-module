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

namespace ZfrOAuth2ModuleTest\Server\Mvc;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfrOAuth2Module\Server\Mvc\AuthorizationVaryListener;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @covers ZfrOAuth2Module\Server\Mvc\AuthorizationVaryListener
 */
class AuthorizationVaryListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testDoNotTouchResponseForRequestsWithoutAuthorization()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest(new HttpRequest());

        $response = new HttpResponse();
        $mvcEvent->setResponse($response);

        $listener = new AuthorizationVaryListener();
        $listener->alterVaryHeader($mvcEvent);

        $this->assertFalse($response->getHeaders()->has('Vary'));
    }

    public function testAppendAuthorizationToVaryIfAlreadyExists()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Authorization', 'abc');

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest($request);

        $response = new HttpResponse();
        $response->getHeaders()->addHeaderLine('Vary', 'Origin');
        $mvcEvent->setResponse($response);

        $listener = new AuthorizationVaryListener();
        $listener->alterVaryHeader($mvcEvent);

        $this->assertTrue($response->getHeaders()->has('Vary'));
        $this->assertEquals('Origin, Authorization', $response->getHeaders()->get('Vary')->getFieldValue());
    }

    public function testAddAuthorizationToVaryIfNotExists()
    {
        $request = new HttpRequest();
        $request->getHeaders()->addHeaderLine('Authorization', 'abc');

        $mvcEvent = new MvcEvent();
        $mvcEvent->setRequest($request);

        $response = new HttpResponse();
        $mvcEvent->setResponse($response);

        $listener = new AuthorizationVaryListener();
        $listener->alterVaryHeader($mvcEvent);

        $this->assertTrue($response->getHeaders()->has('Vary'));
        $this->assertEquals('Authorization', $response->getHeaders()->get('Vary')->getFieldValue());
    }
}
