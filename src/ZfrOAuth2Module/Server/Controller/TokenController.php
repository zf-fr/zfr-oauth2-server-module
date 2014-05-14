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

namespace ZfrOAuth2Module\Server\Controller;

use Zend\Console\Request as ConsoleRequest;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractActionController;
use ZfrOAuth2\Server\AuthorizationServer;
use ZfrOAuth2Module\Server\Event\TokenEvent;
use ZfrOAuth2Module\Server\Exception\RuntimeException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class TokenController extends AbstractActionController
{
    /**
     * @var AuthorizationServer
     */
    protected $authorizationServer;

    /**
     * @param AuthorizationServer $authorizationServer
     */
    public function __construct(AuthorizationServer $authorizationServer)
    {
        $this->authorizationServer = $authorizationServer;
    }

    /**
     * Handle a token request
     *
     * @return \Zend\Http\Response|null
     */
    public function tokenAction()
    {
        $request = $this->getRequest();

        // Can't do anything if not HTTP request...
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $response = $this->authorizationServer->handleTokenRequest($request);

        // We extract the body so that it is easier to modify in the event
        $responseBody = json_decode($response->getContent(), true);
        $event        = new TokenEvent($request, $responseBody, $response->getMetadata('accessToken', null));

        if ($response->isSuccess()) {
            $this->getEventManager()->trigger(TokenEvent::EVENT_TOKEN_CREATED, $event);
        } else {
            $this->getEventManager()->trigger(TokenEvent::EVENT_TOKEN_FAILED, $event);
        }

        // We re-encode the response into the body
        $response->setContent(json_encode($event->getResponseBody()));

        return $response;
    }

    /**
     * Delete expired tokens
     *
     * @return string
     * @throws RuntimeException
     */
    public function deleteExpiredTokensAction()
    {
        if (!$this->request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from console');
        }

        /* @var \ZfrOAuth2\Server\Service\TokenService $accessTokenService */
        $accessTokenService = $this->serviceLocator->get('ZfrOAuth2\Server\Service\AccessTokenService');
        $accessTokenService->deleteExpiredTokens();

        /* @var \ZfrOAuth2\Server\Service\TokenService $refreshTokenService */
        $refreshTokenService = $this->serviceLocator->get('ZfrOAuth2\Server\Service\RefreshTokenService');
        $refreshTokenService->deleteExpiredTokens();

        /* @var \ZfrOAuth2\Server\Service\TokenService $authorizationCodeService */
        $authorizationCodeService = $this->serviceLocator->get('ZfrOAuth2\Server\Service\AuthorizationCodeService');
        $authorizationCodeService->deleteExpiredTokens();

        return "\nExpired tokens were properly deleted!\n\n";
    }
}
