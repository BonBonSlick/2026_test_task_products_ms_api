<?php

declare(strict_types=1);

namespace App\Infrastructure\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

    private string $env;

    public function __construct(private readonly LoggerInterface $logger, ParameterBagInterface $parameterBag) {
        $this->env = $parameterBag->get('kernel.environment');
    }

    public static function getSubscribedEvents(): array {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void {
        if ('prod' === $this->env) {
            $exception = $event->getThrowable();
            $this->logger->error(
                $exception->getMessage(),
                [
                    'exception' => $exception,
                    'trace'     => $exception->getTraceAsString(),
                ],
            );
        }
    }

}
