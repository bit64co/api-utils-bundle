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
final class ContentControl extends AbstractAnnotation {

	/** @var bool */
	protected $json_pretty_print;

	/** @var bool */
	protected $parse_request_data;

	/** @var array<string> */
	protected $response_encoding;

}
