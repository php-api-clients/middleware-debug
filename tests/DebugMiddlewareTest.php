<?php declare(strict_types=1);

namespace ApiClients\Tests\Middleware\Debug;

use ApiClients\Middleware\Debug\DebugMiddleware;
use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use React\Stream\ThroughStream;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\Response;
use function Clue\React\Block\await;
use function React\Promise\Stream\buffer;

final class DebugMiddlewareTest extends TestCase
{
    public function testAllMethods()
    {
        $expectedStdout = '';
        $expectedStdout .= DebugMiddleware::HR;
        $expectedStdout .= 'abc: request' . PHP_EOL;
        $expectedStdout .= DebugMiddleware::HR;
        $expectedStdout .= 'Method: GET' . PHP_EOL;
        $expectedStdout .= 'URI: foo.bar' . PHP_EOL;
        $expectedStdout .= DebugMiddleware::HR;
        $expectedStdout .= DebugMiddleware::HR;
        $expectedStdout .= 'abc: response' . PHP_EOL;
        $expectedStdout .= DebugMiddleware::HR;
        $expectedStdout .= 'Status: 200' . PHP_EOL;
        $expectedStdout .= DebugMiddleware::HR;

        $request = new Request('GET', 'foo.bar');
        $response = new Response();

        $throughStream = new ThroughStream();
        $promise = buffer($throughStream);

        $loop = Factory::create();
        $middleware = new DebugMiddleware($loop, $throughStream);

        $middleware->pre($request, 'abc', []);
        $middleware->post($response, 'abc', []);

        $throughStream->close();

        $stdout = await($promise, $loop);
        self::assertSame($expectedStdout, $stdout);
    }
}
