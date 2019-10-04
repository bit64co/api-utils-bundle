<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Security;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class FirewallListener implements ListenerInterface {

	protected $configs, $tokens, $authManager;

	public function __construct(array $configs, TokenStorageInterface $tokens, AuthenticationManagerInterface $authManager) {
		$this->configs = $configs;
		$this->tokens = $tokens;
		$this->authManager = $authManager;
	}

	public function handle(GetResponseEvent $event): void {

		$request = $event->getRequest();
		$auth_header = $this->configs['auth_header'] ?? 'X-Auth';

		if (!$request->headers->has($auth_header)) return;

		$pattern = '/token="(?P<token>[^"]+)", nonce="(?P<nonce>[^"]+)", digest="(?P<digest>[^"]+)", date="(?P<date>[^"]+)"/';
		if (!preg_match($pattern, $request->headers->get($auth_header), $matches)) return;

		$token = new AuthenticationToken;
		$token->setUser($matches['token']);

		list($token->nonce, $token->digest, $token->date) = [$matches['nonce'], $matches['digest'], $matches['date']];

		$authToken = $this->authManager->authenticate($token);
		$this->tokens->setToken($authToken);

	}

}
