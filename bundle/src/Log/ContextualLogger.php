<?php

namespace Hyvor\Internal\Bundle\Log;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Usage:
 *
 * ```php
 * private function __construct(LoggerInterface $logger)
 * {}
 *
 * public function doSomething(): void
 * {
 *     $logger = ContextualLogger::from($this->logger, ['base' => 'context']);
 *     $logger->info('This is a contextual log message', ['additional' => 'context']);
 * }
 * ```
 */
#[Exclude]
class ContextualLogger implements LoggerInterface
{

    /**
     * @param array<string, mixed> $baseContext
     */
    public static function from(LoggerInterface $logger, array $baseContext = []): self
    {
        return new self($logger, $baseContext);
    }

    /**
     * @param class-string $handleClass
     * @param array<string, mixed> $additionalBaseContext
     */
    public static function forMessageHandler(
        LoggerInterface $logger,
        string $handleClass,
        array $additionalBaseContext = []
    ): self {
        $classNameLastParts = explode('\\', $handleClass);
        $classNameLastPart = end($classNameLastParts);

        $id = uniqid(); // to easily filter logs for a run

        return new self($logger, array_merge([
            'message_handler' => $classNameLastPart,
            'message_handler_id' => $id,
        ], $additionalBaseContext));
    }

    /**
     * @param array<string, mixed> $baseContext
     */
    public function __construct(
        private LoggerInterface $logger,
        private readonly array $baseContext = []
    ) {
    }

    /**
     * @param array<string, mixed> $context
     * @return mixed[]
     */
    private function getContext(array $context): array
    {
        return array_merge($this->baseContext, $context);
    }

    public function emergency(\Stringable|string $message, array $context = []): void
    {
        $this->logger->emergency($message, $this->getContext($context));
    }

    public function alert(\Stringable|string $message, array $context = []): void
    {
        $this->logger->alert($message, $this->getContext($context));
    }

    public function critical(\Stringable|string $message, array $context = []): void
    {
        $this->logger->critical($message, $this->getContext($context));
    }

    public function error(\Stringable|string $message, array $context = []): void
    {
        $this->logger->error($message, $this->getContext($context));
    }

    public function warning(\Stringable|string $message, array $context = []): void
    {
        $this->logger->warning($message, $this->getContext($context));
    }

    public function notice(\Stringable|string $message, array $context = []): void
    {
        $this->logger->notice($message, $this->getContext($context));
    }

    public function info(\Stringable|string $message, array $context = []): void
    {
        $this->logger->info($message, $this->getContext($context));
    }

    public function debug(\Stringable|string $message, array $context = []): void
    {
        $this->logger->debug($message, $this->getContext($context));
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $this->getContext($context));
    }
}