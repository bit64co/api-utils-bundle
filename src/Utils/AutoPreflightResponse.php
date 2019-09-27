<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Utils;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class AutoPreflightResponse extends Response {

	private $config;

	public function setAccessControlConfiguration(array $config): void {
		$this->config = $config;
	}

	public function getAccessControlConfiguration(): ?array {
		return $this->config;
	}

}
