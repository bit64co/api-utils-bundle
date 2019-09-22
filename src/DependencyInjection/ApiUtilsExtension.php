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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ApiUtilsExtension extends Extension {

	public function load(array $configs, ContainerBuilder $container) {

		$configuration = new Configuration;
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');

	}

}
