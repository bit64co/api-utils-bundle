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
		$node = $treeBuilder->root('api_utils');

		$node
			->children()
				->arrayNode('content_control')
					->addDefaultsIfNotSet()
					->info('Content configuration')
					->children()
						->booleanNode('json_pretty_print')
							->defaultValue(false)
							->info('Apply JSON_PRETTY_PRINT formatting to JsonResponses')
						->end()
						->scalarNode('response_encoding')
							->defaultValue('br, deflate, gzip')
							->info('Response content encoding mode(s)')
						->end()
						->booleanNode('parse_request_data')
							->defaultValue(true)
							->info('Parse request data where applicable')
						->end()
					->end()
				->end()
				->arrayNode('access_control')
					->addDefaultsIfNotSet()
					->info('Cross Origin Resource Sharing (CORS) default configuration')
					->children()
						->booleanNode('auto_preflight')
							->defaultValue(true)
							->info('Automatically respond to OPTIONS requests with an empty response')
						->end()
						->scalarNode('allow_origin')
							->defaultValue('{origin}')
							->info('Allow from origin, use {origin} to quote the client-defined origin')
						->end()
						->booleanNode('allow_credentials')
							->defaultValue(true)
							->info('Can the resource be exposed using credentials, for preflights this indicates whether the request can be made with credentials')
						->end()
						->arrayNode('allow_methods')
							->scalarPrototype()->end()
							->info('Which request methods are allowed to be used in the request')
						->end()
						->arrayNode('allow_headers')
							->scalarPrototype()->end()
							->info('Which request headers are allowed to be used in the request')
						->end()
						->arrayNode('expose_headers')
							->scalarPrototype()->end()
							->info('Which response headers should be exposed to the client')
						->end()
						->integerNode('max_age')
							->defaultValue(7200)
							->info('How long the Access-Control-Allow-Headers/Methods from a preflight can be cached')
						->end()
					->end()
				->end()
				->arrayNode('view_handlers')
					->addDefaultsIfNotSet()
					->info('Available view resolvers for controller results')
					->children()
						->booleanNode('json_array')
							->defaultValue(true)
							->info('Resolve native arrays as a JsonResponse')
						->end()
						->booleanNode('json_object')
							->defaultValue(true)
							->info('Resolve instances of JsonSerializable as a JsonResponse')
						->end()
						->booleanNode('json_form_errors')
							->defaultValue(true)
							->info('Extract all FormError objects from a Symfony FormInterface and resolve as a JsonResponse')
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

}
