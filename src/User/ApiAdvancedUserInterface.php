<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\User;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
interface ApiAdvancedUserInterface extends ApiUserInterface, AdvancedUserInterface {

}
