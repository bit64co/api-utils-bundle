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
use Exception;
use JsonSerializable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Warren Heyneke <hello@bit64.co>
 */
class ExceptionSubscriber implements EventSubscriberInterface {

	private $config = [], $translator;

	public function __construct(ApiUtilities $utils, TranslatorInterface $translator = null) {

		$this->config = $utils->getConfigNode('exceptions');
		$this->translator = $translator;

	}

	public static function getSubscribedEvents(): array {
		return [
			KernelEvents::EXCEPTION => 'onKernelException',
		];
	}

	public function onKernelException(GetResponseForExceptionEvent $event): void {

		$request = $event->getRequest();
		$format = $request->get('_format');

		if (('json' === $format || null === $format) && true === ($this->config['json_error_responses'] ?? true)) {
			$event->setResponse($this->createJsonExceptionResponse($event->getException()));
		}

	}

	private function createJsonExceptionResponse(Exception $exception): JsonResponse {

		list($status_code, $message, $headers) = $this->getExceptionResponseData($exception);

		return new JsonResponse([
			'error' => [
				'code' => $status_code,
				'message' => $message,
			],
		], $status_code, $headers);

	}

	private function getExceptionResponseData(Exception $exception): array {

		$data = [
			'status_code' => 500,
			'message' => "Technical Error: This service is currently unavailable",
			'translation_domain' => null,
		];

		$previous_stack = [];
		if ($prev = $exception->getPrevious()) {
			$previous_stack[] = get_class($prev);
			while ($prev = $prev->getPrevious()) {
				$previous_stack[] = get_class($prev);
			}
		}
		$previous_stack = array_unique($previous_stack);

		if ($exception instanceof HttpExceptionInterface) {

			$data['status_code'] = $exception->getStatusCode();
			$data['message'] = $exception->getMessage();
			$data['headers'] = $exception->getHeaders();

		}

		foreach ($this->config['overrides'] ?? [] as $e) {
			if (
				in_array(get_class($exception), $e['exceptions'] ?? []) ||
				(($e['check_previous_stack'] ?? false) && count(array_intersect($previous_stack, $e['exceptions'] ?? [])))
			) {
				$data = [
					'status_code' => preg_replace('/^{code}$/', $data['status_code'], $e['status_code'] ?? $data['status_code']),
					'message' => preg_replace('/^{message}$/', $data['message'], $e['message'] ?? $data['message']),
					'translation_domain' => $e['translation_domain'] ?? $data['translation_domain'],
				];
				break;
			}
		}

		// Translate if available
		if (!empty($data['translation_domain'] ?? null) && $this->translator instanceof TranslatorInterface) {
			$data['message'] = $this->translator->trans($data['message'], [
				'%status_code%' => $data['status_code'],
				'%message%' => $data['message'],
			], $data['translation_domain']);
		}

		// Include exception details in debug headers?
		if (true === ($this->config['error_response_debug_headers'] ?? null)) {

			// Access control expose headers, so that client applications can access them
			$data['headers']['Access-Control-Expose-Headers'] = implode(',', array_unique(
				array_merge(
					array_filter(
						is_array($data['headers']['Access-Control-Expose-Headers'] ?? '') ?
						$data['headers']['Access-Control-Expose-Headers'] :
						explode(',', $data['headers']['Access-Control-Expose-Headers'] ?? '')
					), ['X-Debug-ExceptionStack','X-Debug-ExceptionMessage']
				)
			));

			// Add debug exception details to headers array
			$data['headers']['X-Debug-ExceptionStack'] = implode('; ', array_merge([get_class($exception)], $previous_stack));
			$data['headers']['X-Debug-ExceptionMessage'] = sprintf(
				"%s in file %s on line %u",
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine()
			);

		}

		return [$data['status_code'], $data['message'], $data['headers'] ?? []];
	}

	private function getExceptionConfig(Exception $exception, $code = null): array {

		return $config;

	}

}
