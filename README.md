# Tempest Worker Test

Code for benchmarking performance of different servers using Tempest.

## Usage

```
docker compose up -d
```

Then run the tests:
```bash
# php test.php [requests]
php test.php 1000
```

## Hosts
- `server` - frankenphp normal php server
- `worker` - frankenphp using worker mode
- `local` - php local server (`php -S 0.0.0.0:8000 -t public`)
- `fpm` - nginx + fpm stack

## Tests
- `simple` - call simple endpoint returning OK HTTP 200, 1000 times sequentially
- `parallel` - call the same endpoint, but 1000 times in parallel using CURL MULTIEXEC
- `users` - call endpoint that fetches 1000 records from sqlite database, 1000 times sequentially

## Results

```
Test: simple
  Host: worker (http://localhost:8022/), RPS: 1065.8589020975, Performance: 100.00%
  Host: local (http://localhost:8023/), RPS: 99.707460401606, Performance: 9.35%
  Host: fpm (http://localhost:8024/), RPS: 99.5571759523, Performance: 9.34%
  Host: server (http://localhost:8021/), RPS: 88.14129492327, Performance: 8.27%
Test: parallel
  Host: worker (http://localhost:8022/), RPS: 4405.6889561144, Performance: 100.00%
  Host: server (http://localhost:8021/), RPS: 545.38414027605, Performance: 12.38%
  Host: fpm (http://localhost:8024/), RPS: 217.6073846256, Performance: 4.94%
  Host: local (http://localhost:8023/), RPS: 106.42554088751, Performance: 2.42%
Test: users
  Host: worker (http://localhost:8022/), RPS: 48.906527440473, Performance: 100.00%
  Host: local (http://localhost:8023/), RPS: 37.03832500002, Performance: 75.73%
  Host: fpm (http://localhost:8024/), RPS: 36.205543846147, Performance: 74.03%
  Host: server (http://localhost:8021/), RPS: 32.502051959839, Performance: 66.46%
```