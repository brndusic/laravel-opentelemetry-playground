# Laravel OpenTelemetry playground
This repository is inspired by https://github.com/open-telemetry/opentelemetry-php/blob/main/docs/laravel-quickstart.md 
It goes a bit deeper in defining spans and usage of OpenTelemetry tools in controllers

## Google Cloud Traces&Logging
Create service account with permissions for writing logs and traces and create credentails for it. Download .json credentials
and place them in `storage/credentials.json`. Check the .example.env for Google project environment variables.

If tracing and logging does not work the biggest chance is that credentials and permissions are not correctly set up.
Currently hard to debug because tracer is instantiated outside Laravel scope. This implementation should be moved to 
Laravel lifecycle if possible.

## Startup instructions

```shell
cd docker
cp .env.example .env

// replace UID and USER_NAME in .env file with ones from your system
// UID of current user can be found by runnning following commant
id -u 

docker-compose up -d

// System is up and running
```

## Using composer

```shell
docker-compose run --rm composer install
```

## Access through webpage
For better experience edit `hosts` file and add
```shell
127.0.0.1 otel.test
```
Now navigate to http://otel.test:8083/

### Checklist

----------
#### Source
```shell
index.php
app/Http/Controllers/HelloController.php
```
----------

#### Tools
http://localhost:9411/ Zipkin

http://localhost:16686/ Jaeger

Play with Jaeger and Zipkin after visiting http://127.0.0.1:8000 and http://127.0.0.1:8000/hello
----------
