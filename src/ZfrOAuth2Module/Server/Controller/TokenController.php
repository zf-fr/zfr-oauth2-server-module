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
        // Can't do anything if not HTTP request...
        if (!$this->request instanceof HttpRequest) {
            return;
        }

        return $this->authorizationServer->handleTokenRequest($this->request);
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

        return 'Expired access tokens were properly deleted';
    }
}
