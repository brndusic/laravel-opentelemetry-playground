<?php

namespace App\Traits;

use \OpenTelemetry\API\Trace\TracerInterface;

trait TracedJob
{
    public string $traceId;

    public function setTraceId(string $traceId){
        $this->traceId = $traceId;
        /** @var TracerInterface $tracer */
        $tracer = app()->make(TracerInterface::class);
        $tracer->setTraceId($traceId);
    }
}
