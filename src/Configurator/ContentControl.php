<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Configurator;

use Bit64\Bundle\ApiUtilsBundle\Annotation\ContentControl as Annotation;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
final class ContentControl extends AbstractConfigurator {

	public function getRouteConfiguration(Request $request): array {

		$annotations = $config = [];

		if (is_array($ac = $request->attributes->get('_content_control', []))) {
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

		return array_merge($this->getConfig('content_control'), $config, $annotations);

	}

}
