<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\RouteConfigurator;

use Bit64\Bundle\ApiUtilsBundle\Annotation\AccessControl as Annotation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
final class AccessControl extends AbstractRouteConfigurator {

	public function getRouteConfiguration(Request $request): array {

		$annotations = $config = [];

		if (is_array($ac = $request->attributes->get('_access_control', []))) {
			foreach ($ac as $k => $v) {
				$config[$k] = $v;
			}
		}

		if ($request->attributes->has('_controller')) {

			list($controller, $method) = explode('::', $request->get('_controller'));

			foreach (array_filter(
					[
						$this->getClassAnnotation($controller, Annotation::class),
						$this->getMethodAnnotation($controller, $method, Annotation::class),
					]
				) as $annotation) {
				foreach ($annotation as $k => $v) {
					$annotations[$k] = $v;
				}
			}

		}

		foreach (['allow_headers', 'expose_headers'] as $multiple) {
			$config[$multiple] = array_unique(array_merge($config[$multiple] ?? [], $annotations[$multiple] ?? []));
			unset($annotations[$multiple]);
		}

		return array_merge($this->getConfig('access_control'), $config, $annotations);

	}

}
