<?php

declare(strict_types=1);
/**
 * Application By zrone.
 *
 * @link     https://gitee.com/marksirl
 * @document https://gitee.com/marksirl
 * @contact  zrone<xujining415@gmail.com>
 */
namespace App;

use ConstantUtil\Utils\Arr;
use Entity\CryptDataConfig;

/**
 * gitee 签名验证
 *
 * Class GiteeCrypt
 */
class GiteeCrypt extends AbstractCrypt
{
    public $secret;

    public $token;

    public $timestamp;

    public $sign;

    public function __construct(CryptDataConfig $headerPreValidatorData)
    {
        parent::__construct($headerPreValidatorData);

        $this->secret = Arr::get($headerPreValidatorData->config, 'SECRET');
        $this->token = $headerPreValidatorData->headers->get('x-gitee-token');
        $this->timestamp = $headerPreValidatorData->headers->get('x-gitee-timestamp');
    }

    /**
     * 签名.
     */
    public function buildPrefixCryptSign(): self
    {
        $prefixCryptString = <<<STR
{$this->timestamp}
{$this->secret}
STR;

        $this->sign = base64_encode(hash_hmac('sha256', $prefixCryptString, $this->secret, true));
        return $this;
    }
}
