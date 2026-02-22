<?php

class Request
{
    public function __construct(
        public string $url,
        public string $method = 'GET',
        public array $headers = [],
        public ?array $body = null,
    ) {}
}

class Response
{
    public function __construct(
        public int $status,
        public string $body,
        public array $headers = [],
    ) {}
}

class Requester
{
    public function request(Request $request): Response
    {
        $curl = curl_init($request->url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (!empty($request->headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $request->headers);
        }

        if ($request->body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request->body));
        }

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);

        return new Response(
            status: $status,
            body: $response ?: '',
            headers: $headers ?: [],
        );
    }

    /**
     * @param Request[] $requests
     * 
     * @return Response[]
     */
    public function requests(array $requests): array
    {
        // use curl_multi to send multiple requests in parallel
        $multiCurl = curl_multi_init();
        $curlHandles = [];
        foreach ($requests as $index => $request) {
            $curl = curl_init($request->url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request->method);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            if (!empty($request->headers)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $request->headers);
            }

            if ($request->body !== null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request->body));
            }

            curl_multi_add_handle($multiCurl, $curl);
            $curlHandles[$index] = $curl;
        }

        $running = null;
        do {
            curl_multi_exec($multiCurl, $running);
            curl_multi_select($multiCurl);
        } while ($running > 0);

        $responses = [];

        foreach ($curlHandles as $index => $curl) {
            $response = curl_multi_getcontent($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT);

            $responses[$index] = new Response(
                status: $status,
                body: $response ?: '',
                headers: $headers ?: [],
            );

            curl_multi_remove_handle($multiCurl, $curl);
        }

        curl_multi_close($multiCurl);

        return $responses;
    }
}

class TestSuiteResults
{
    /**
     * @param HostTestResults[] $results
     */
    public function __construct(
        public array $results = [],
    ) {}

    public function print(): void
    {
        $performanceOrder = [];

        foreach ($this->results as $hostResult) {
            foreach ($hostResult->results as $result) {
                if (!isset($performanceOrder[$result->test])) {
                    $performanceOrder[$result->test] = [];
                }

                $performanceOrder[$result->test][] = [
                    'name' => $hostResult->name,
                    'host' => $hostResult->host,
                    'rps' => $result->rps,
                ];
            }
        }

        foreach ($performanceOrder as $test => $hosts) {
            usort($hosts, fn($a, $b) => $b['rps'] <=> $a['rps']);
            echo "Test: {$test}\n";

            foreach ($hosts as $host) {
                $percentage = ($host['rps'] / $hosts[0]['rps']) * 100;
                echo "  Host: {$host['name']} ({$host['host']}), RPS: {$host['rps']}, Performance: " . number_format($percentage, 2) . "%\n";
            }
        }

    }
}

class HostTestResults
{
    /**
     * @param TestResult[] $results
     */
    public function __construct(
        public string $name,
        public string $host,
        public array $results = [],
    ) {}
}

class TestResult
{
    public function __construct(
        public string $test,
        public float $rps,
    ) {}
}

class Tester
{
    public function __construct(
        private Requester $requester,
        private array $hosts,
        private int $times,
    ) {}

    public function runSuite(): TestSuiteResults
    {
        $results = new TestSuiteResults();

        foreach ($this->hosts as $name => $host) {
            $results->results[] = $this->runSuiteForHost($name, $host);
        }

        return $results;
    }

    private function runSuiteForHost(string $name, string $host): HostTestResults
    {
        $results = new HostTestResults($name, $host);

        $results->results[] = new TestResult(
            test: 'simple',
            rps: $this->measure(fn() => $this->testSimple($host)),
        );
        $results->results[] = new TestResult(
            test: 'parallel',
            rps: $this->measure(fn() => $this->testParallel($host)),
        );
        $results->results[] = new TestResult(
            test: 'users',
            rps: $this->measure(fn() => $this->testUsers($host)),
        );
        $results->results[] = new TestResult(
            test: 'users parallel',
            rps: $this->measure(fn() => $this->testUsersParallel($host)),
        );
        $results->results[] = new TestResult(
            test: 'echo',
            rps: $this->measure(fn() => $this->testEcho($host)),
        );
        $results->results[] = new TestResult(
            test: 'echo parallel',
            rps: $this->measure(fn() => $this->testEchoParallel($host)),
        );

        return $results;
    }

    private function measure(callable $callback): float
    {
        $start = microtime(true);
        $callback();
        $duration = microtime(true) - $start;
        
        return $this->times / $duration;
    }

    private function testSimple(string $host): void
    {
        $request = new Request(url: $host . '/');

        foreach (range(1, $this->times) as $index) {
            $response = $this->requester->request($request);

            if ($response->status !== 200) {
                throw new Exception("Simple request $index failed with status: {$response->status}");
            }
        }
    }

    private function testParallel(string $host): void
    {
        $requests = array_fill(0, $this->times, new Request(url: $host));
        $responses = $this->requester->requests($requests);

        foreach ($responses as $index => $response) {
            if ($response->status !== 200) {
                throw new Exception("Parallel request $index failed with status: {$response->status}");
            }
        }
    }

    private function testUsers(string $host): void
    {
        $request = new Request(url: $host . '/users');

        foreach (range(1, $this->times) as $index) {
            $response = $this->requester->request($request);

            if ($response->status !== 200) {
                throw new Exception("Users request $index failed with status: {$response->status}");
            }
        }
    }

    private function testUsersParallel(string $host): void
    {
        $requests = array_fill(0, $this->times, new Request(url: $host . '/users'));
        $responses = $this->requester->requests($requests);

        foreach ($responses as $index => $response) {
            if ($response->status !== 200) {
                throw new Exception("Users parallel request $index failed with status: {$response->status}");
            }
        }
    }

    private function testEcho(string $host): void
    {
        foreach (range(1, $this->times) as $index) {
            $request = new Request(
                url: $host . '/echo',
                method: 'POST',
                body: ['message' => $index],
            );
            $response = $this->requester->request($request);

            if ($response->status !== 200) {
                throw new Exception("Echo request $index failed with status: {$response->status}");
            }

            $responseData = json_decode($response->body, true);
            if ($responseData['message'] !== $index) {
                throw new Exception("Echo request $index failed with unexpected response: {$response->body}");
            }
        }
    }

    private function testEchoParallel(string $host): void
    {
        $requests = array_map(
            fn ($index) => new Request(
                url: $host . '/echo',
                method: 'POST',
                body: ['message' => $index],
            ),
            range(1, $this->times),
        );

        $responses = $this->requester->requests($requests);

        foreach ($responses as $index => $response) {
            if ($response->status !== 200) {
                throw new Exception("Echo parallel request $index failed with status: {$response->status}");
            }

            $responseData = json_decode($response->body, true);
            if ($responseData['message'] !== $index + 1) {
                throw new Exception("Echo parallel request $index failed with unexpected response: {$response->body}");
            }
        }
    }
}

$hosts = [
    'server' => 'http://localhost:8021',
    'worker' => 'http://localhost:8022',
    'local' => 'http://localhost:8023',
    'fpm' => 'http://localhost:8024',
];
$tester = new Requester();
$times = 100;

if (isset($argv[1]) && is_numeric($argv[1])) {
    $times = (int) $argv[1];
}

$testSuite = new Tester(requester: $tester, hosts: $hosts, times: $times);
$results = $testSuite->runSuite();

$results->print();