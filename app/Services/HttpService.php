<?php


namespace App\Services;


use Swoole\Http\Server;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class HttpService
{
    private $http = null;
    private $conf = array(
        'enable_static_handler' => true,
        'document_root' => __DIR__ . '/../../public'
    );

    private $host = '0.0.0.0';
    private $port = '80';

    private $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    public function __construct()
    {
    }

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

    public function onRequest($req, $res)
    {
//        define('LARAVEL_START', microtime(true));
        require __DIR__ . '/../../vendor/autoload.php';
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle(
            $request = \Illuminate\Http\Request::createFromBase($this->createFromGlobals($req))
        );
        // headers
        foreach ($response->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $res->header($name, $value);
            }
        }
        // cookies
        foreach ($response->headers->getCookies() as $cookie) {
            $res->header('Set-Cookie', $cookie);
        }
        // status
        $res->status($response->getStatusCode(), isset($this->statusTexts[$response->getStatusCode()]) ? $this->statusTexts[$response->getStatusCode()] : 'unknown status');

        $res->end($response->getContent());
        $kernel->terminate($request, $response);

    }

    public function createFromGlobals($req)
    {
        $request = new Request($req->get ?? [], $req->post ?? [], [], $req->cookie ?? [], $req->files ?? [], $req->server ?? [], null);
        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && \in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }
        return $request;
    }

    public function start()
    {
        $this->http->on("Request", array($this, 'onRequest'));
        $this->http->start();
    }
}
