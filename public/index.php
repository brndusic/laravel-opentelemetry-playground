<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/


$httpClient = new Client();
$httpFactory = new HttpFactory();

$tracer = (new TracerProvider(
    [
        new SimpleSpanProcessor(
            new OpenTelemetry\Contrib\Jaeger\Exporter(
                'Hello World Web Server Jaeger',
                'http://localhost:9412/api/v2/spans',
                $httpClient,
                $httpFactory,
                $httpFactory,
            ),
        ),
//        new BatchSpanProcessor(
        new SimpleSpanProcessor(
            new OpenTelemetry\Contrib\Zipkin\Exporter(
                'Hello World Web Server Zipkin',
                'http://localhost:9411/api/v2/spans',
                $httpClient,
                $httpFactory,
                $httpFactory,
            ),
//            new \OpenTelemetry\SDK\Common\Time\SystemClock()
        ),
    ],
    new AlwaysOnSampler(),
))->getTracer('Hello World Laravel Web Server');

$request = Request::capture();
$span = $tracer->spanBuilder($request->url())->startSpan();
$span->setAttribute('foo', 'bar');
$span->setAttribute('Application', 'Laravel');
$span->setAttribute('foo', 'bar1');
$span->updateName('New name');

$spanScope = $span->activate();

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$app->singleton(\OpenTelemetry\SDK\Trace\Tracer::class, function () use ($tracer){
    return $tracer;
});

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

$span->end();
$spanScope->detach();
