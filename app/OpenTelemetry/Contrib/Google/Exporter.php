<?php

namespace App\OpenTelemetry\Contrib\Google;

use Google\Cloud\Trace\TraceClient;
use OpenTelemetry\API\Trace\SpanInterface;
use Opentelemetry\Proto\Trace\V1\Span;
use OpenTelemetry\SDK\ClockInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Trace\ImmutableSpan;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class Exporter implements SpanExporterInterface
{
    private TraceClient $traceClient;

    public function __construct(private string $keyFilePath)
    {
        $this->traceClient = new TraceClient([
            'keyFilePath' => $this->keyFilePath
        ]);
    }

    public static function fromConnectionString(string $endpointUrl, string $name, string $args)
    {
    }

    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $googleSpans = array_map(function (ImmutableSpan $span) {
            return SpanConverter::convertSpan($span);
        }, (array) $spans);

        $trace = $this->traceClient->trace($googleSpans[0]->traceId());
        $trace->setSpans($googleSpans);

        try {
            $this->traceClient->insert($trace);
        } catch (\Throwable $exception) {
            echo $exception->getMessage();
        }

        //TODO: Respect return type
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        // TODO: Implement shutdown() method.
        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        // TODO: Implement forceFlush() method.
        return true;
    }
}
