<?php


namespace App\Services;


use Swoole\Http\Server;

class HttpService
{
    private $http = null;
    private $conf = array(
        'enable_static_handler' => true,
        'document_root' => __DIR__ . '/../../public'
    );
    private $host = '0.0.0.0';
    private $port = '80';

    /**
     * @param string $host
     * @return HttpService
     */
    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param string $port
     * @return HttpService
     */
    public function setPort(string $port): self
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return $this
     */
    public function init(): self
    {
        $this->http = new Server($this->host, $this->port);
        $this->http->set($this->conf);
        return $this;
    }

    /**
     * @param array $set
     * @return $this
     *
     */
    public function httpConf(array $set): self
    {
        $this->conf = array_merge($this->conf, $set);
        $this->http->set($this->conf);
        return $this;
    }

    /**
     * @param $req
     * @param $res
     */
    public function onRequest($req, $res)
    {
        !defined('LARAVEL_START') && define('LARAVEL_START', microtime(true));
        require __DIR__ . '/../../vendor/autoload.php';
        $app = require __DIR__ . '/../../bootstrap/app.php';
        (new \Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($app);
        $this->initRequest($req);
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(
            $request = \Illuminate\Http\Request::capture()
        );
        $this->send($response, $res);
        $kernel->terminate($request, $response);
    }

    /**
     * @param $request
     */
    private function initRequest($request)
    {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];
        foreach ($request->server as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
        }
        foreach ($request->header as $key => $value) {
            $_SERVER['HTTP_' . strtoupper($key)] = $value;
        }
    }

    /**
     * @param $response
     * @param $res
     */
    private function send($response, $res)
    {
        $response->sendHeaders();
        $res->end($response->getContent());
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            \Symfony\Component\HttpFoundation\Response::closeOutputBuffers(0, true);
        }
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->http->on("Request", array($this, 'onRequest'));
        $this->http->start();
        return $this;
    }
}
