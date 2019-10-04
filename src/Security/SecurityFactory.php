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

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class SecurityFactory implements SecurityFactoryInterface {

	public function create(ContainerBuilder $container, $id, $configs, $userProvider, $defaultEntryPoint) {

		$providerId = sprintf('security.authentication.provider.api_utils_wsse.%s', $id);
		$container
			->setDefinition($providerId, new ChildDefinition(AuthenticationProvider::class))
			->setArgument(0, $configs)
			->setArgument(1, new Reference($userProvider));

		$listenerId = sprintf('security.authentication.listener.api_utils_wsse.%s', $id);
		$container
			->setDefinition($listenerId, new ChildDefinition(FirewallListener::class))
			->setArgument(0, $configs);

		return [$providerId, $listenerId, $defaultEntryPoint];

	}

	public function getPosition(): string {
		return 'pre_auth';
	}

	public function getKey(): string {
		return 'api_utils_wsse';
	}

	public function addConfiguration(NodeDefinition $node): void {
		$node
			->info('WSSE firewall configuration')
			->children()
				->scalarNode('auth_header')
					->info('Authentication request header name')
					->defaultValue('X-Auth')
				->end()
				->scalarNode('error_header')
					->info('Authentication error message response header name')
					->defaultValue('X-AuthError')
				->end()
				->booleanNode('send_error_header')
					->info('Send authentication error message via http response header')
					->defaultValue(false)
				->end()
				->booleanNode('anti_replay')
					->info('Prevent replay attacks by caching nonce values')
					->defaultValue(true)
				->end()
				->integerNode('expired_threshold')
					->info('Threshold (in seconds) for allowing expired requests dated behind the current server time')
					->defaultValue(60)
				->end()
				->integerNode('premature_threshold')
					->info('Threshold (in seconds) for allowing premature requests dated ahead of the current server time')
					->defaultValue(60)
				->end()
			->end();
	}

}
