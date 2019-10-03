<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\DependencyInjection;

use Bit64\Bundle\ApiUtilsBundle\Services\ApiUtilities;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ApiUtilsExtension extends Extension {

	public function load(array $configs, ContainerBuilder $container) {

		$configuration = new Configuration;
		$config = $this->processConfiguration($configuration, $configs);
		$this->registerResponseOverridesConfig($container->getParameter('kernel.project_dir'), $config);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yaml');

		$container->getDefinition(ApiUtilities::class)->setArgument(0, $config);

	}

	private function registerResponseOverridesConfig(string $project_dir, array &$config): void {

		# First check if {project_dir}/config/exceptions/error_response_overrides.yaml exists
		$error_response_overrides_file = realpath(sprintf("%s/config/exceptions/error_response_overrides.yaml", $project_dir));

		if (false === $error_response_overrides_file) {

			# Fallback to the bundle default found
			$error_response_overrides_file = realpath(sprintf("%s/../Resources/config/exceptions/error_response_overrides.yaml", __DIR__));

		}

		$config['exceptions'] = array_merge($config['exceptions'] ?? [], Yaml::parseFile($error_response_overrides_file));

	}

}
