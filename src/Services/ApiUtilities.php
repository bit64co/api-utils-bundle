<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Services;

use Bit64\Bundle\ApiUtilsBundle\RouteConfigurator\AccessControl;
use Bit64\Bundle\ApiUtilsBundle\RouteConfigurator\ContentControl;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
final class ApiUtilities {

	private $configs, $annotation_reader;

	public function __construct(array $configs, Reader $annotation_reader) {
		$this->configs = $configs;
		$this->annotation_reader = $annotation_reader;
	}

	public function getConfigNode(string $node): array {
		return $this->configs[$node] ?? [];
	}

	public function getAccessControlConfigurationForRoute(Request $request): array {
		return (new AccessControl($this->configs, $this->annotation_reader))->getRouteConfiguration($request);
	}

	public function getContentControlConfigurationForRoute(Request $request): array {
		return (new ContentControl($this->configs, $this->annotation_reader))->getRouteConfiguration($request);
	}

}
