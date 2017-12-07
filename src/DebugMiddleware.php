<?php declare(strict_types=1);

namespace ApiClients\Middleware\Debug;

use ApiClients\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\LoopInterface;
use React\Promise\CancellablePromiseInterface;
use React\Stream\WritableResourceStream;
use React\Stream\WritableStreamInterface;
use Throwable;
use function React\Promise\reject;
use function React\Promise\resolve;

final class DebugMiddleware implements MiddlewareInterface
{
    const HR = '---------------------------------------' . PHP_EOL;

    /**
     * @var WritableStreamInterface
     */
    private $stdout;

    public function __construct(LoopInterface $loop, WritableStreamInterface $stdout = null)
    {
        if ($stdout === null) {
            $stdout = new WritableResourceStream(STDOUT, $loop);
        }

        $this->stdout = $stdout;
    }

    /**
     * Return the processed $request via a fulfilled promise.
     * When implementing cache or other feature that returns a response, do it with a rejected promise.
     * If neither is possible, e.g. on some kind of failure, resolve the unaltered request.
     *
     * @param  RequestInterface            $request
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function pre(RequestInterface $request, string $transactionId, array $options = []): CancellablePromiseInterface
    {
        $this->stdout->write(self::HR);
        $this->stdout->write($transactionId . ': request' . PHP_EOL);
        $this->stdout->write(self::HR);
        $this->stdout->write('Method: ' . $request->getMethod() . PHP_EOL);
        $this->stdout->write('URI: ' . (string)$request->getUri() . PHP_EOL);
        $this->stdout->write(self::HR);

        return resolve($request);
    }

    /**
     * Return the processed $response via a promise.
     *
     * @param  ResponseInterface           $response
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function post(ResponseInterface $response, string $transactionId, array $options = []): CancellablePromiseInterface
    {
        $this->stdout->write(self::HR);
        $this->stdout->write($transactionId . ': response' . PHP_EOL);
        $this->stdout->write(self::HR);
        $this->stdout->write('Status: ' . $response->getStatusCode() . PHP_EOL);
        $this->stdout->write(self::HR);

        return resolve($response);
    }

    /**
     * Deal with possible errors that occurred during request/response events.
     *
     * @param  Throwable                   $throwable
     * @param  string                      $transactionId
     * @param  array                       $options
     * @return CancellablePromiseInterface
     */
    public function error(Throwable $throwable, string $transactionId, array $options = []): CancellablePromiseInterface
    {
        $this->stdout->write(self::HR);
        $this->stdout->write($transactionId . ': error' . PHP_EOL);
        $this->stdout->write(self::HR);
        $this->stdout->write((string)$throwable);
        $this->stdout->write(self::HR);

        return reject($throwable);
    }
}
