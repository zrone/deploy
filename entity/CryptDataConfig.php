<?php

declare(strict_types=1);
/**
 * Application By zrone.
 *
 * @link     https://gitee.com/marksirl
 * @document https://gitee.com/marksirl
 * @contact  zrone<xujining415@gmail.com>
 */

namespace Entity;

use App\RepositoryEnum;
use Symfony\Component\HttpFoundation\HeaderBag;

class CryptDataConfig
{
    /** @var int */
    public $symbol;

    /** @var string */
    public $htmlUrl;

    /** @var HeaderBag */
    public $headers;

    /** @var array */
    public $config;

    /** @var string */
    public $payload;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            if (property_exists(__CLASS__, $key)) {
                $method = 'set' . ucfirst($key);

                method_exists($this, $method) &&
                is_callable(array(
                    $this,
                    $method,
                )) &&
                $this->$method($val);
            }
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\HeaderBag
     */
    public function getHeaders(): HeaderBag
    {
        return $this->headers;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\HeaderBag $headers
     */
    public function setHeaders(HeaderBag $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * @return int
     */
    public function getSymbol(): int
    {
        return $this->symbol;
    }

    /**
     * @param int $symbol
     */
    public function setSymbol(int $symbol): void
    {
        $this->symbol = $symbol;
    }

    /**
     * @return string
     */
    public function getHtmlUrl(): string
    {
        return $this->htmlUrl;
    }

    /**
     * @param string $htmlUrl
     */
    public function setHtmlUrl(string $htmlUrl): void
    {
        if (preg_match("/^https:\/\/github.com(.*)/", $htmlUrl)) {
            $this->symbol = RepositoryEnum::GITHUB;
        } else {
            $this->symbol = RepositoryEnum::GITEE;
        }
        $this->htmlUrl = $htmlUrl;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @param string $payload
     */
    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }
}
