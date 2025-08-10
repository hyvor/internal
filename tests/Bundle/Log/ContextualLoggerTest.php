<?php

namespace Hyvor\Internal\Tests\Bundle\Log;

use Hyvor\Internal\Bundle\Log\ContextualLogger;
use Hyvor\Internal\Tests\SymfonyTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;

#[CoversClass(ContextualLogger::class)]
class ContextualLoggerTest extends SymfonyTestCase
{

    public function test_from(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $baseContext = ['base' => 'context'];
        $contextualLogger = ContextualLogger::from($logger, $baseContext);

        $methods = [
            'debug',
            'info',
            'notice',
            'warning',
            'error',
            'critical',
            'alert',
            'emergency'
        ];

        foreach ($methods as $method) {
            $logger->expects($this->once())
                ->method($method)
                ->with(
                    $this->callback(function ($message) {
                        return $message === 'Test message';
                    }),
                    $this->callback(function ($context) {
                        return $context === [
                                'base' => 'context',
                                'additional' => 'context'
                            ];
                    })
                );

            $contextualLogger->{$method}('Test message', ['additional' => 'context']);
        }
    }

    public function test_for_message_handler(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $baseContext = ['base' => 'context'];
        // @phpstan-ignore-next-line
        $contextualLogger = ContextualLogger::forMessageHandler($logger, 'App\\My\\TestMessageHandler', $baseContext);

        $logger->expects($this->once())
            ->method('log')
            ->with(
                $this->stringContains('debug'),
                $this->stringContains('test_handler'),
                $this->callback(function ($context) {
                    return is_array($context) &&
                        $context['base'] === 'context' &&
                        $context['message_handler'] === 'TestMessageHandler' &&
                        is_string($context['message_handler_id']) &&
                        $context['more'] === 'context';
                })
            );

        $contextualLogger->log('debug', 'test_handler', ['more' => 'context']);
    }

}