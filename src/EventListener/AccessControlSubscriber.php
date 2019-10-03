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

use Bit64\Bundle\ApiUtilsBundle\Services\ApiUtilities;
use Bit64\Bundle\ApiUtilsBundle\Http\AutoPreflightResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class AccessControlSubscriber implements EventSubscriberInterface {

	private $utils, $matcher, $router, $reader;

	public function __construct(UrlMatcherInterface $matcher, RouterInterface $router, ApiUtilities $utils) {
		$this->utils = $utils;
		$this->matcher = $matcher;
		$this->router = $router;
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::REQUEST => [
				['autoPreflight', 33],
			],
			KernelEvents::RESPONSE => [
				['accessControlHeaders', 0],
			],
		];
	}

	public function autoPreflight(GetResponseEvent $event): void {

		$request = $event->getRequest();
		if ('OPTIONS' === $request->getMethod()) {

			$params = $this->matcher->matchRequest($request);
			$lookup = $request->duplicate();
			$lookup->attributes->replace($params);
			$config = $this->utils->getAccessControlConfigurationForRoute($lookup);

			if ($config['auto_preflight'] && isset($params['_route'])) {

				$request->attributes->replace($params);
				$route = $this->router->getRouteCollection()->get($request->get('_route'));

				if ($route instanceof Route) {
					$config['allow_methods'] = empty($config['allow_methods']) ? $route->getMethods() : $config['allow_methods'];
					$response = new AutoPreflightResponse('');
					$response->setAccessControlConfiguration($config);
					$event->setResponse($response);
				}

			}

		}

	}

	public function accessControlHeaders(FilterResponseEvent $event): void {

		$request = $event->getRequest();
		$response = $event->getResponse();

		if ('OPTIONS' === $request->getMethod()) {
			# Check auto preflight response
			if ($response instanceof AutoPreflightResponse) {
				$config = $response->getAccessControlConfiguration();
			}
		} else {
			# Remove preflight configs while not in pre-flight mode
			$config = $this->utils->getAccessControlConfigurationForRoute($request);
			unset($config['allow_headers']);
			unset($config['allow_methods']);
			unset($config['max_age']);
		}

		# Replace "{origin}" with client-defined origin
		if (preg_match('/^{origin}$/i', $config['allow_origin'] ?? null)) {
			$origin = $request->headers->get('Origin');
			$config['allow_origin'] = preg_replace('/^null$/i', '*', empty($origin) ? 'null' : $origin);
		}

		# Apply credentials only when true
		$config['allow_credentials'] = true === ($config['allow_credentials'] ?? false) ? 'true' : null;

		foreach ([
			'Access-Control-Allow-Origin' => $config['allow_origin'] ?? null,
			'Access-Control-Allow-Credentials' => $config['allow_credentials'] ?? null,
			'Access-Control-Max-Age' => $config['max_age'] ?? null,
		] as $header => $value) {
			if (null !== $value && !$response->headers->has($header)) {
				$response->headers->set($header, $value);
			}
		}

		foreach ([
			'Access-Control-Allow-Methods' => $config['allow_methods'] ?? [],
			'Access-Control-Allow-Headers' => $config['allow_headers'] ?? [],
			'Access-Control-Expose-Headers' => $config['expose_headers'] ?? [],
		] as $header => $values) {
			if (!empty($values)) {
				if (!is_array($values)) {
					$values = array_filter(array_map('trim', explode(',', $values)));
				}
				$response->headers->set($header, $values, false);
			}
		}

	}

}
