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

namespace ZfrOAuth2Module\Server\Authentication\Adapter;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result as AuthenticationResult;
use Zend\Http\Request as HttpRequest;
use ZfrOAuth2\Server\ResourceServer;
use ZfrOAuth2Module\Server\Exception\RuntimeException;

/**
 * Authenticate a user using an access token
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class AccessTokenAdapter implements AdapterInterface
{
    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * @param ResourceServer $resourceServer
     */
    public function __construct(ResourceServer $resourceServer)
    {
        $this->resourceServer = $resourceServer;
    }

    /**
     * Set the HTTP request
     *
     * @param  HttpRequest $request
     * @return void
     */
    public function setRequest(HttpRequest $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        if (null === $this->request) {
            throw new RuntimeException('Request must be set in order to authenticate against an access token');
        }

        if (!$this->resourceServer->isRequestValid($this->request)) {
            return new AuthenticationResult(
                AuthenticationResult::FAILURE,
                null,
                ['You are not authorized to perform this action']
            );
        }

        $accessToken = $this->resourceServer->getAccessToken($this->request);

        return new AuthenticationResult(AuthenticationResult::SUCCESS, $accessToken->getOwner());
    }
}
