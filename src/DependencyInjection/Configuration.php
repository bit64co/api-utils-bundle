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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class Configuration implements ConfigurationInterface {

	public function getConfigTreeBuilder() {

		$treeBuilder = new TreeBuilder;
		$node = $treeBuilder->root('bit64_api_utils');

		$node
			->children()
				->arrayNode('view_handlers')
					->info('Available view resolvers for controller results')
					->isRequired()
					->children()
						->booleanNode('json_array')
							->isRequired()
							->defaultValue(true)
							->info('Resolve native arrays as a JsonResponse')
						->end()
						->booleanNode('json_object')
							->isRequired()
							->defaultValue(true)
							->info('Resolve instances of JsonSerializable as a JsonResponse')
						->end()
						->booleanNode('json_form_errors')
							->isRequired()
							->defaultValue(true)
							->info('Extract all FormError objects from a Symfony FormInterface and resolve as a JsonResponse')
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

}
