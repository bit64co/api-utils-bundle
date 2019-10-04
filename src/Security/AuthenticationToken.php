<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class AuthenticationToken extends AbstractToken {

	public $nonce, $digest, $date;

	public function __construct(array $roles = []) {
		parent::__construct($roles);
		$this->setAuthenticated(0 < count($roles));
	}

	public function getCredentials(): string {
		return '';
	}

}
