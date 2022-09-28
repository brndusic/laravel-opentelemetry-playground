<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use OpenTelemetry\SDK\Trace\Tracer;

class HelloController extends Controller
{
    public function index(Tracer $tracer)
    {
        $childSpan  = $tracer->spanBuilder('Child span 1')->startSpan();
        $childScope = $childSpan->activate();
        Log::shareContext([
            'stackdriverOptions' => [
                'trace' => sprintf('projects/%s/traces/%s', config('logging.googleProjectId'), $childSpan->getContext()->getTraceId()),
            ]
        ]);
        try {
            Log::debug('Before exception', ['spanId' => $childSpan->getContext()->getSpanId()]);

            throw new \Exception('Exception Example');
        } catch (\Exception $exception) {
            Log::debug('Exception catch', ['spanId' => $childSpan->getContext()->getSpanId()]);
            $childSpan->recordException($exception);
        }
        $childSpan->end();
        $childScope->detach();

        // Start new span here

        $childSpan  = $tracer->spanBuilder('Child span 2')->startSpan();
        $childScope = $childSpan->activate();

        Log::warning('Log from new span', ['spanId' => $childSpan->getContext()->getSpanId()]);

        $childSpan->end();
        $childScope->detach();

        return 'hello';
    }
}
