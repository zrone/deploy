<?php

declare(strict_types=1);
/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */

namespace App;

use Entity\CryptDataConfig;

/**
 * gitee 签名验证
 *
 * Class GiteeCrypt
 */
class GiteeCrypt
{
    public $secret;

    public $token;

    public $timestamp;

    public $sign;

    public $package;

    public function __construct(CryptDataConfig $headerPreValidatorData)
    {
        $this->secret = $headerPreValidatorData->config['SECRET'];
        $this->token = $headerPreValidatorData->token;
        $this->timestamp = $headerPreValidatorData->timestamp;
    }

    public static function build(CryptDataConfig $headerPreValidatorData): GiteeCrypt
    {
        return new static($headerPreValidatorData);
    }

    /**
     * 签名.
     */
    public function buildPrefixCryptSign(): self
    {
        // $timestamp = bcmul((string) microtime(true), (string) 1000);
        $timestamp = $this->timestamp;
        $prefixCryptString = <<<STR
{$timestamp}
{$this->secret}
STR;

        $this->sign = base64_encode(hash_hmac('sha256', $prefixCryptString, $this->secret, true));
        return $this;
    }

    /**
     * 验证签名.
     */
    public function compare(): bool
    {
        return $this->sign === $this->token;
    }
}
