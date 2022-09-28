<?php

namespace App\OpenTelemetry\Contrib\Google;

use Google\Cloud\Trace\Annotation;
use Google\Cloud\Trace\Link;
use Google\Cloud\Trace\Span;
use Google\Cloud\Trace\Status;
use Google\Rpc\Code;
use OpenTelemetry\SDK\ClockInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\ImmutableSpan;
use OpenTelemetry\SDK\Trace\LinkInterface;
use OpenTelemetry\SDK\Trace\StatusDataInterface;

class SpanConverter
{
//    const ATTRIBUTE_MAP = [
//        OCSpan::ATTRIBUTE_HOST        => '/http/host',
//        OCSpan::ATTRIBUTE_PORT        => '/http/port',
//        OCSpan::ATTRIBUTE_METHOD      => '/http/method',
//        OCSpan::ATTRIBUTE_PATH        => '/http/url',
//        OCSpan::ATTRIBUTE_USER_AGENT  => '/http/user_agent',
//        OCSpan::ATTRIBUTE_STATUS_CODE => '/http/status_code'
//    ];
//    const LINK_TYPE_MAP = [
//        OCLink::TYPE_UNSPECIFIED        => Link::TYPE_UNSPECIFIED,
//        OCLink::TYPE_CHILD_LINKED_SPAN  => Link::TYPE_CHILD_LINKED_SPAN,
//        OCLink::TYPE_PARENT_LINKED_SPAN => Link::TYPE_PARENT_LINKED_SPAN
//    ];
//    const MESSAGE_TYPE_MAP = [
//        OCMessageEvent::TYPE_UNSPECIFIED => MessageEvent::TYPE_UNSPECIFIED,
//        OCMessageEvent::TYPE_SENT        => MessageEvent::TYPE_SENT,
//        OCMessageEvent::TYPE_RECEIVED    => MessageEvent::TYPE_RECEIVED
//    ];

    const AGENT_KEY = 'g.co/agent';
    const AGENT_STRING = 'opentelemetry-php-google-cloud-trace-exporter';

    /**
     * Convert an OpenTelemetry ImmutableSpan to its Google Cloud Trace representation.
     *
     * @access private
     *
     * @param  ImmutableSpan  $span  The span to convert.
     *
     * @return Span
     */
    public static function convertSpan(ImmutableSpan $span)
    {
        $startTimestamp = TimeUtil::nanosToMillis($span->getStartEpochNanos()) / ClockInterface::MILLIS_PER_SECOND;
        $endTimestamp = TimeUtil::nanosToMillis($span->getEndEpochNanos()) / ClockInterface::MILLIS_PER_SECOND;

        $spanOptions = [
            'name'       => $span->getName(),
            'startTime'  => $startTimestamp,
            'endTime'    => $endTimestamp,
            'spanId'     => $span->getSpanId(),
            'attributes' => self::convertAttributes($span->getAttributes()),
//            'stackTrace' => $span->stackTrace(),
            'links'      => self::convertLinks($span->getLinks(), $span->getTraceId(), $span->getSpanId()),
            'timeEvents' => self::convertTimeEvents($span->getEvents()),
            'status'     => self::convertStatus($span->getStatus())
        ];
        if ($span->getParentSpanId()) {
            $spanOptions['parentSpanId'] = $span->getParentSpanId();
        }

        return new Span($span->getTraceId(), $spanOptions);
    }

    private static function convertAttributes(AttributesInterface $attributes)
    {
        $newAttributes = [
            self::AGENT_KEY => self::AGENT_STRING
        ];
        foreach ($attributes as $key => $value) {
//            if (array_key_exists($key, self::ATTRIBUTE_MAP)) {
//                $newAttributes[self::ATTRIBUTE_MAP[$key]] = $value;
//            } else {
                $newAttributes[$key] = $value;
//            }
        }

        return $newAttributes;
    }

    /**
     * @param  LinkInterface[]  $links
     *
     * @return Link[]
     */
    private static function convertLinks(array $links, string $traceId, string $spanId): array
    {
        return array_map(function (LinkInterface $link) use ($spanId, $traceId) {
            return new Link($traceId, $spanId, [
                'type' => Link::TYPE_UNSPECIFIED,
//                'type'       => self::LINK_TYPE_MAP[$link->type()],
                'attributes' => $link->getAttributes()->toArray()
            ]);
        }, $links);
    }

    /**
     * @param  EventInterface[]  $events
     *
     * @return array
     */
    private static function convertTimeEvents(array $events): array
    {
        $newEvents = [];
        foreach ($events as $event) {
//            if ($event instanceof OCAnnotation) {
                $newEvents[] = self::convertAnnotation($event);
//            } elseif ($event instanceof OCMessageEvent) {
//                $newEvents[] = self::convertMessageEvent($event);
//            }
        }

        return $newEvents;
    }

    private static function convertAnnotation(EventInterface $annotation): Annotation
    {
        $time = TimeUtil::nanosToMicros($annotation->getEpochNanos()) / ClockInterface::MICROS_PER_SECOND;

        return new Annotation($annotation->getAttributes()->get('description'), [
            'attributes' => $annotation->getAttributes()->toArray(),
            'time'       => $time
        ]);
    }

//    private static function convertMessageEvent(EventInterface $messageEvent)
//    {
//        return new MessageEvent($messageEvent->id(), [
//            'type'                  => self::MESSAGE_TYPE_MAP[$messageEvent->type()],
//            'uncompressedSizeBytes' => $messageEvent->uncompressedSize(),
//            'compressedSizeBytes'   => $messageEvent->compressedSize(),
//            'time'                  => $messageEvent->time()
//        ]);
//    }

    private static function convertStatus(StatusDataInterface $status = null): ?Status
    {
        if ($status) {
            return new Status(Code::OK, $status->getDescription());
        } else {
            return null;
        }
    }
}
