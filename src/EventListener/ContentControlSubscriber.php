<?php
/*
 * This file is part of the Bit 64 API Utility Bundle for Symfony
 *
 * Copyright © 2019 Bit 64 Solutions (Pty) Ltd <hello@bit64.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Bit64\Bundle\ApiUtilsBundle\EventListener;

use Bit64\Bundle\ApiUtilsBundle\Configurator\ContentControl;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ContentControlSubscriber implements EventSubscriberInterface {

	private $config;

	public function __construct(ContentControl $configurator) {
		$this->config = $configurator;
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [
				['parseRequestContent', 0],
			],
			KernelEvents::RESPONSE => [
				['prettyPrintJsonFormatting', 0],
				['encodeResponseContent', -1024],
			],
		];
	}

	public function parseRequestContent(GetResponseEvent $event): void {
		$request = $event->getRequest();
		if ($request->headers->has('Content-Type')) {
			$config = $this->config->getRouteConfiguration($request);
			if ($config['parse_request_data'] ?? false) {
				$type = $request->headers->get('Content-Type');
				switch (true) {
					case preg_match("/application\/json/i", $type) :
						$this->parseEncodedJsonContent($request);
					break;
				}
			}
		}
	}

	public function prettyPrintJsonFormatting(FilterResponseEvent $event): void {
		$response = $event->getResponse();
		if ($response instanceof JsonResponse) {
			$request = $event->getRequest();
			$config = $this->config->getRouteConfiguration($request);
			if ($config['json_pretty_print'] ?? false) {
				$response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
			}
		}
	}

	public function encodeResponseContent(FilterResponseEvent $event): void {

		$request = $event->getRequest();
		$response = $event->getResponse();

		if (!$response->headers->has('Content-Encoding') && $request->headers->has('Accept-Encoding')) {

			$config = $this->config->getRouteConfiguration($request);
			$modes = $config['response_encoding'] ?? [];
			if (!is_array($modes)) {
				$modes = array_filter(array_map('strtolower', array_map('trim', explode(',', $modes))));
			}
			$accept = array_filter(array_map('strtolower', array_map('trim', explode(',', $request->headers->get('Accept-Encoding')))));

			foreach (array_intersect($modes, $accept) as $mode) {
				if (!$response->headers->has('Content-Encoding')) {
					switch (true) {

						# See https://github.com/kjdev/php-ext-brotli
						case 'br' === $mode && function_exists('brotli_compress') :
							$response->headers->set('Content-Encoding', $mode);
							$response->setContent(brotli_compress($response->getContent()));
						break;

						# See https://www.php.net/manual/en/function.gzdeflate.php
						case 'deflate' === $mode && function_exists('gzdeflate') :
							$response->headers->set('Content-Encoding', $mode);
							$response->setContent(gzdeflate($response->getContent()));
						break;

						# See https://www.php.net/manual/en/function.gzencode.php
						case 'gzip' === $mode && function_exists('gzencode') :
							$response->headers->set('Content-Encoding', $mode);
							$response->setContent(gzencode($response->getContent()));
						break;

					}
				} else {
					break;
				}
			}

		}

	}

	private function parseEncodedJsonContent(Request $request): void {
		if (!empty($content = $this->decodeRequestContent($request))) {
			$content = json_decode($content, true);
			switch (json_last_error()) {
				case JSON_ERROR_NONE : $request->request->replace($content); break;
				default : throw new HttpException(400, "Invalid or malformed JSON"); break;
			}
		}
	}

	private function decodeRequestContent(Request $request): string {

		$content = $request->getContent();

		switch (true) {

			case $request->headers->has('Content-Encoding') :

				$encoding = $request->headers->get('Content-Encoding');

				if (preg_match("/base64/i", $encoding)) {
					$content = base64_decode($content);
				}

				switch (true) {
					case preg_match("/identity/i", $encoding) : break;
					case preg_match("/br/i", $encoding) && function_exists('brotli_uncompress') :
						try {$content = brotli_uncompress($content);}
						catch(Exception $e) {throw new HttpException(400, "Failed to decode (brotli) request content");}
					break;
					case preg_match("/deflate/i", $encoding) && function_exists('gzinflate') :
						try {$content = gzinflate($content);}
						catch(Exception $e) {throw new HttpException(400, "Failed to decode (deflate) request content");}
					break;
					case preg_match("/gzip/i", $encoding) && function_exists('gzdecode') :
						try {$content = gzdecode($content);}
						catch(Exception $e) {throw new HttpException(400, "Failed to decode (gzip) request content");}
					break;
					default :
						throw new HttpException(400, sprintf("Content encoding is not supported (%s)", $encoding));
					break;
				}

			break;
		}

		return $content;

	}

}
