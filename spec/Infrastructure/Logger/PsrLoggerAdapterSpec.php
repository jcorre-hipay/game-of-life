<?php

declare(strict_types=1);

namespace spec\GameOfLife\Infrastructure\Logger;

use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class PsrLoggerAdapterSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    function it_forwards_an_emergency_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->emergency('World war III has been declared.', ['advice' => 'run!'])->shouldBeCalled();

        $this->emergency('World war III has been declared.', ['advice' => 'run!']);
    }

    function it_forwards_an_alert_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->alert('Database is down.', ['query' => 'SELECT 1'])->shouldBeCalled();

        $this->alert('Database is down.', ['query' => 'SELECT 1']);
    }

    function it_forwards_a_critical_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->critical('Service provider API unavailable.', ['api' => 'fraud detection'])->shouldBeCalled();

        $this->critical('Service provider API unavailable.', ['api' => 'fraud detection']);
    }

    function it_forwards_an_error_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->error('Incoherent payment status.', ['status' => 'authorized'])->shouldBeCalled();

        $this->error('Incoherent payment status.', ['status' => 'authorized']);
    }

    function it_forwards_a_warning_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->warning('Deprecated encryption method.', ['cipher' => 'sha1'])->shouldBeCalled();

        $this->warning('Deprecated encryption method.', ['cipher' => 'sha1']);
    }

    function it_forwards_a_notice_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->notice('Daily threshold has been reached.', ['threshold' => 500])->shouldBeCalled();

        $this->notice('Daily threshold has been reached.', ['threshold' => 500]);
    }

    function it_forwards_an_info_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->info('User logged in.', ['email' => 'catmaster@mail.com'])->shouldBeCalled();

        $this->info('User logged in.', ['email' => 'catmaster@mail.com']);
    }

    function it_forwards_a_debug_log_to_its_internal_logger(
        LoggerInterface $logger
    ) {
        $logger->debug('Applying fetching strategy.', ['strategy' => 'recursive'])->shouldBeCalled();

        $this->debug('Applying fetching strategy.', ['strategy' => 'recursive']);
    }
}
