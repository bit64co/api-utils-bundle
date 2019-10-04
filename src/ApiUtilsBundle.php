<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle;

use Bit64\Bundle\ApiUtilsBundle\Security\SecurityFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ApiUtilsBundle extends Bundle {

	public function build(ContainerBuilder $container) {
		$extension = $container->getExtension('security');
		$extension->addSecurityListenerFactory(new SecurityFactory);
	}

}
