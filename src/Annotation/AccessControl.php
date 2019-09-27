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

/**
 * @author Warren Heyneke <hello@bit64.co>
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 */
final class AccessControl extends AbstractAnnotation {

	/** @var bool */
	protected $auto_preflight;

	/** @var string */
	protected $allow_origin;

	/** @var bool */
	protected $allow_credentials;

	/** @var array<string> */
	protected $allow_methods;

	/** @var array<string> */
	protected $allow_headers;

	/** @var array<string> */
	protected $expose_headers;

	/** @var int */
	protected $max_age;

}
