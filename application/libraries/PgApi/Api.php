<?php

namespace Pg\Libraries\PgApi;

class Api
{
    const API_SECTION = 'api/';

    /**
     * Api host
     *
     * @var string
     */
    private $host;

    /**
     * Api token
     *
     * @var string
     */
    private $token = null;

    public function __construct($host, $token = null)
    {
        $this->setHost($host);
        if (!empty($token)) {
            $this->token = $token;
        }
    }

    /**
     * Get api host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set api host
     *
     * @param string $host
     *
     * @return \Pg\Libraries\PgApi\Api
     */
    public function setHost($host)
    {
        if (($temp = strlen($host) - 1) < 0 || strpos($host, '/', $temp) === false) {
            $host .= '/';
        }
        $this->host = $host;

        return $this;
    }

    /**
     * Build url by request params
     *
     * @param string $module
     * @param string $method
     *
     * @return string
     */
    protected function buildUrl($module = '', $method = '')
    {
        $tail = '';
        if ($module) {
            $tail .= $module . '/';
            if ($method) {
                $tail .= $method . '/';
            }
        }

        return $this->getHost() . self::API_SECTION . $tail;
    }

    /**
     * Execute request
     *
     * @param array  $query
     * @param string $request_tool
     *
     * @return array
     */
    public function doRequest(array $query, $request_tool = null)
    {
        $url = $this->buildUrl(get($query['module']), get($query['method']));

        return Request::send($url, get($query['get']), get($query['post']), $request_tool);
    }
}
