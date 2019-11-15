<?php


namespace App\Services;


use Swoole\Http\Server;
use swoole_http_server;
use swoole_server;

class HttpService
{
    private $http = null;
    private $conf = array(
        'enable_static_handler' => true,
        'document_root' => __DIR__ . '/../../public',
        'worker_num' => 5

    );
    private $host = '0.0.0.0';
    private $port = '80';
    private $app;

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
        $this->http = new swoole_http_server($this->host, $this->port);
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
        $this->initRequest($req);
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        ob_start();
        $response = $kernel->handle(
            $request = \Illuminate\Http\Request::capture()
        );
        $response->send();
        $content = ob_get_contents();
        ob_end_clean();
        $kernel->terminate($request, $response);
        $res->end($content);

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
     * @param swoole_server $server
     * @param int $worker_id
     */
    public function onWorkerStart(swoole_server $server, int $worker_id)
    {
        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/../../vendor/autoload.php';
        $this->app = require_once __DIR__ . '/../../bootstrap/app.php';
//        (new \Illuminate\Foundation\Bootstrap\LoadConfiguration)->bootstrap($this->app);
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->http->on("Request", array($this, 'onRequest'));
        $this->http->on("WorkerStart", array($this, 'onWorkerStart'));
        $this->http->start();
        return $this;
    }
}
