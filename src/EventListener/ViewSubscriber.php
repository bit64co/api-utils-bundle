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

	private $configs;

	public function __construct(array $configs) {
		$this->configs = [];
	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::VIEW => 'onKernelView',
		];
	}

	public function onKernelView(GetResponseForControllerResultEvent $event): void {

		$cfg = isset($this->configs['view_handlers']) ? $this->configs['view_handlers'] : [];
		$result = $event->getControllerResult();
		$request = $event->getRequest();
		$format = $request->get('_format');

		if ('json' == $format || null === $format) {

			if (
				((!isset($cfg['json_array']) || $cfg['json_array']) && is_array($result)) ||
				((!isset($cfg['json_object']) || $cfg['json_object']) && $result instanceof JsonSerializable)
			) {
				$event->setResponse(new JsonResponse($result));
			}

		}

	}

}
