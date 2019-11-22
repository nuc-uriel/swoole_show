<?php


namespace App\Services;


use Str;
use swoole_http_server;
use swoole_server;
use Symfony\Component\VarDumper\VarDumper;
use Redis;

class WebSocketService
{
    private $http = null;
    private $conf = array(
        'worker_num' => 5,

    );
    private $host = '0.0.0.0';
    private $port = '8888';
    private $app;
    /**
     * @var resource
     */
    private $notify;

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
    public function setConf(array $set): self
    {
        $this->conf = array_merge($this->conf, $set);
        $this->http->set($this->conf);
        return $this;
    }

    /**
     * @param $req
     * @param $res
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onOpen($req, $res)
    {
        if (require_once __DIR__ . '/../../bootstrap/app.php') {
            $this->app = app();
        }
        $this->initRequest($req);
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        ob_start();
        $response = $kernel->handle(
            $request = \Illuminate\Http\Request::capture()
        );
        $response->send();
        $content = ob_get_contents();
        ob_end_clean();
        $this->initResponse($response, $res);
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
        // 设置调试组件
        VarDumper::setHandler();
        $_SERVER['VAR_DUMPER_FORMAT'] = 'html';
    }

    /**
     * @param $response
     * @param $res
     */
    private function initResponse($response, &$res)
    {
        // 添加header头
        $headers = $response->headers->allPreserveCaseWithoutCookies();
        foreach ($headers as $key => $value) {
            $res->header($key, $value[0]);
        }
        // 添加cookie
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            $res->cookie($cookie->getName(),
                $cookie->getValue() ?? '',
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain() ?? '',
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->getSameSite() ?? '');
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
        if ($worker_id == 0) {
            // 设置热更新目录
            $this->notify = inotify_init();
            // 排除目录
            $except = [
                '.',
                '..',
                'vendor',
                'storage',
                'node_modules',
                '.idea'
            ];
            $add_watch = function ($dir) use (&$add_watch, $except) {
                inotify_add_watch($this->notify, $dir, IN_CREATE | IN_DELETE | IN_MODIFY);
                $list = scandir($dir);
                foreach ($list as $sub_dir) {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $sub_dir) && !in_array($sub_dir, $except)) {
                        $add_watch($dir . DIRECTORY_SEPARATOR . $sub_dir);
                    }
                }
            };
            $add_watch(realpath('../..'));
            swoole_event_add($this->notify, function () use ($server) {
                $events = inotify_read($this->notify);
                if (!empty($events)) {
                    $server->reload();
                }
            });
        }
    }

    public function onWorkerExit($server, $worker_id)
    {
        swoole_event_del($this->notify);
    }

    /**
     * @return $this
     */
    public function start()
    {
        $this->http->on("Request", array($this, 'onRequest'));
        $this->http->on("WorkerStart", array($this, 'onWorkerStart'));
        $this->http->on("WorkerExit", array($this, 'onWorkerExit'));
        $this->http->start();
        return $this;
    }

    public function addKey($userId)
    {
        $token = hash('sha256', Str::random(60));
        if (Redis::get('u2t:' . $userId)) {
            Redis::del(Redis::get('u2t:' . $userId));
        }
        Redis::setex('u2t:' . $userId, config('session.lifetime', 120) * 60, $token);
        Redis::setex('t2u:' . $token, config('session.lifetime', 120) * 60, $userId);
    }

    public function removeKey($keyword)
    {
        Redis::del(['t2u:' . Redis::get('u2t:' . $keyword) ?? '',
            'u2t:' . Redis::get('t2u:' . $keyword) ?? '',
            'u2t:' . $keyword,
            't2u:' . $keyword]);
    }

    public function getUser($token)
    {
        return Redis::get('t2u:' . $token);
    }

    public function getTokens($userId)
    {
        return Redis::get('u2t:' . $userId);
    }
}