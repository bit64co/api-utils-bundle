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

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
abstract class AbstractConfigurator {

	private $configs, $reader;

	public function __construct(array $configs = [], Reader $reader) {
		$this->configs = $configs;
		$this->reader = $reader;
	}

	public function getConfig(string $node): array {
		return $this->configs[$node] ?? [];
	}

	public function getClassAnnotation(string $class, string $annotation) {
		$reflectionClass = new ReflectionClass($class);
		return $this->reader->getClassAnnotation($reflectionClass, $annotation);
	}

	public function getMethodAnnotation(string $class, string $method, string $annotation) {
		$reflectionClass = new ReflectionClass($class);
		return $this->reader->getMethodAnnotation($reflectionClass->getMethod($method), $annotation);
	}

}
