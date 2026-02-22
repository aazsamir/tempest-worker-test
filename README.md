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
- `users parallel` - call the same endpoint, but 1000 times in parallel using CURL MULTIEXEC
- `echo` - call endpoint that echoes back the request body, 1000 times sequentially
- `echo parallel` - call the same endpoint, but 1000 times in parallel using CURL MULTIEXEC

## Results

```
Test: simple
  Host: worker (http://localhost:8022), RPS: 1055.1671012983, Performance: 100.00%
  Host: local (http://localhost:8023), RPS: 96.86567787388, Performance: 9.18%
  Host: fpm (http://localhost:8024), RPS: 92.100936451952, Performance: 8.73%
  Host: server (http://localhost:8021), RPS: 88.357682038286, Performance: 8.37%
Test: parallel
  Host: worker (http://localhost:8022), RPS: 4427.0892045539, Performance: 100.00%
  Host: fpm (http://localhost:8024), RPS: 512.60546514658, Performance: 11.58%
  Host: server (http://localhost:8021), RPS: 442.3487077214, Performance: 9.99%
  Host: local (http://localhost:8023), RPS: 100.63471332618, Performance: 2.27%
Test: users
  Host: worker (http://localhost:8022), RPS: 47.536233040406, Performance: 100.00%
  Host: local (http://localhost:8023), RPS: 36.347367183746, Performance: 76.46%
  Host: fpm (http://localhost:8024), RPS: 34.702199761434, Performance: 73.00%
  Host: server (http://localhost:8021), RPS: 31.737028577357, Performance: 66.76%
Test: users parallel
  Host: worker (http://localhost:8022), RPS: 234.80153710525, Performance: 100.00%
  Host: fpm (http://localhost:8024), RPS: 201.1042189988, Performance: 85.65%
  Host: server (http://localhost:8021), RPS: 169.46323360818, Performance: 72.17%
  Host: local (http://localhost:8023), RPS: 37.036573614678, Performance: 15.77%
Test: echo
  Host: worker (http://localhost:8022), RPS: 1015.9844876178, Performance: 100.00%
  Host: local (http://localhost:8023), RPS: 97.714257811262, Performance: 9.62%
  Host: fpm (http://localhost:8024), RPS: 93.914587716182, Performance: 9.24%
  Host: server (http://localhost:8021), RPS: 87.424431756847, Performance: 8.60%
Test: echo parallel
  Host: worker (http://localhost:8022), RPS: 4352.4999636799, Performance: 100.00%
  Host: fpm (http://localhost:8024), RPS: 507.39995364293, Performance: 11.66%
  Host: server (http://localhost:8021), RPS: 469.95287142354, Performance: 10.80%
  Host: local (http://localhost:8023), RPS: 99.925056874416, Performance: 2.30%
```