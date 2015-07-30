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

namespace ZfrOAuth2Module\Server\Mvc;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;

/**
 * This listener automatically adds the Authorization header to the Vary header if it exists. This
 * is needed when you want to cache private resource
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class AuthorizationVaryListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_FINISH, [$this, 'alterVaryHeader']);
    }

    /**
     * @internal
     * @param  MvcEvent $event
     * @return void
     */
    public function alterVaryHeader(MvcEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof HttpRequest || !$request->getHeaders()->has('Authorization')) {
            return;
        }

        /* @var \Zend\Http\Response $response */
        $response = $event->getResponse();
        $headers  = $response->getHeaders();

        if ($headers->has('Vary')) {
            $varyHeader = $headers->get('Vary');
            $varyValue  = $varyHeader->getFieldValue() . ', Authorization';

            $headers->removeHeader($varyHeader);
            $headers->addHeaderLine('Vary', $varyValue);
        } else {
            $headers->addHeaderLine('Vary', 'Authorization');
        }
    }
}
