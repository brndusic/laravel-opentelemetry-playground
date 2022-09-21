## Laravel OpenTelemetry playground
This repository is inspired by https://github.com/open-telemetry/opentelemetry-php/blob/main/docs/laravel-quickstart.md 
It goes a bit deeper in defining spans and usage of OpenTelemetry tools in controllers

### Run project
```shell
docker-compose up -d # runs zipkin and jaeger

composer install
php artisan serve
```

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
