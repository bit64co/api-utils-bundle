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

use Bit64\Bundle\ApiUtilsBundle\User\ApiUserInterface;
use Bit64\Bundle\ApiUtilsBundle\User\ApiUserProviderInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class AuthenticationProvider implements AuthenticationProviderInterface {

	private $configs, $provider, $pool;

	public function __construct(array $configs, UserProviderInterface $provider, ContainerInterface $container) {

		$cachedir = sprintf("%s/bit64co/api_utils", $container->getParameter('kernel.cache_dir'));
		$expired_threshold = $configs['expired_threshold'] ?? 60;

		$this->configs = $configs;
		$this->provider = $provider;
		$this->pool = new FilesystemAdapter('wsse_firewall', $expired_threshold, $cachedir);

	}

	public function authenticate(TokenInterface $token): AuthenticationToken {

		$user = $this->provider->loadUserByApiToken($token->getUsername());

		if ($user instanceof UserInterface) {
			$user = $this->provider->refreshUser($user);
		}

		if (!$user instanceof ApiUserInterface) {
			throw $this->createAuthenticationFailedException("Invalid token");
		}

		if ($this->validateDigest($token, $user->getApiSecret())) {
			$authenticated = new AuthenticationToken($user->getRoles());
			$authenticated->setUser($user);
			return $authenticated;
		}

		throw $this->createAuthenticationFailedException("Unknown Error");

	}

	protected function validateDigest(AuthenticationToken $token, $password): bool {

		$premature_threshold = $this->configs['premature_threshold'] ?? 60;
		$expired_threshold = $this->configs['expired_threshold'] ?? 60;
		$anti_replay = $this->configs['anti_replay'] ?? true;

		$time = strtotime($token->date);

		if ($time - time() > $premature_threshold) {
			# Request date exceeds maximum advance
			throw $this->createAuthenticationFailedException("Request is premature");
		}

		if (time() - $time > $expired_threshold) {
			# Request date is too old
			throw $this->createAuthenticationFailedException("Request has expired");
		}

		if (true === $anti_replay) {
			# Request nonce has already been received, replay is not allowed
			$cache = $this->pool->getItem($token->nonce);
			if ($cache->isHit()) {
				throw $this->createAuthenticationFailedException("Request nonce replayed");
			} else {
				# Cache request nonce to prevent further requests
				$cache->set(null)->expiresAfter($expired_threshold);
				$this->pool->save($cache);
			}
		}

		# Validate digest
		$expected = base64_encode(sha1(sprintf("%s%s%s", $token->nonce, $token->date, $password)));

		if (hash_equals($expected, $token->digest)) {
			return true;
		}

		throw $this->createAuthenticationFailedException("Invalid digest");

	}

	public function supports(TokenInterface $token): bool {
		return $this->provider instanceof ApiUserProviderInterface && $token instanceof AuthenticationToken;
	}

	private function createAuthenticationFailedException(string $error, string $message = "Authentication Failed", int $code = 401) {

		$headers = [];
		$send_error_header = $this->configs['send_error_header'] ?? false;

		if ($send_error_header) {
			$error_header = $this->configs['error_header'] ?? 'X-AuthError';
			$headers = [
				'Access-Control-Expose-Headers' => $error_header,
				$error_header => $error,
			];
		}

		return new HttpException($code, $message, null, $headers);
	}

}
