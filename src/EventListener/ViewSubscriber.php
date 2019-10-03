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

use JsonSerializable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ViewSubscriber implements EventSubscriberInterface {

	private $config;

	public function __construct(array $configs) {
		$this->config = $configs['view_handlers'] ?? [];
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::VIEW => 'onKernelView',
		];
	}

	public function onKernelView(GetResponseForControllerResultEvent $event): void {

		$result = $event->getControllerResult();
		$request = $event->getRequest();
		$format = $request->get('_format');

		if ('json' == $format) {

			if (
				(true === ($this->config['json_array'] ?? false) && is_array($result)) ||
				(true === ($this->config['json_object'] ?? false) && $result instanceof JsonSerializable)
			) {
				$event->setResponse(new JsonResponse($result));
			}

		}

	}

}
