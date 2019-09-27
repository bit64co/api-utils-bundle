<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Annotation;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
abstract class AbstractAnnotation implements IteratorAggregate {

	protected $props = [];

	public function __construct(array $values) {
		foreach ($values as $k => $v) {
			$this->__set($k, $v);
		}
	}

	public function __get($key) {
		if ('props' !== strtolower($key) && property_exists($this, $key)) {
			return $this->{$key};
		}
		return null;
	}

	public function __set($key, $value) {
		if ('props' !== strtolower($key) && property_exists($this, $key)) {
			$this->{$key} = $value;
			$this->props[$key] = $value;
		}
	}

	/**
	 * @return Traversable
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator($this->props);
	}

}
