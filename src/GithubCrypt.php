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
 * github 签名验证
 *
 * Class GithubCrypt
 */
class GithubCrypt extends AbstractCrypt
{
    public $secret;

    public $token;

    public $payload;

    public $sign;

    public function __construct(CryptDataConfig $headerPreValidatorData)
    {
        parent::__construct($headerPreValidatorData);

        $this->secret = Arr::get($headerPreValidatorData->config, 'SECRET');
        $this->token = $headerPreValidatorData->headers->get('x-hub-signature-256');
        $this->payload = $headerPreValidatorData->payload;
    }

    /**
     * 签名.
     */
    public function buildPrefixCryptSign(): CryptInterface
    {
        $this->sign = "sha256=" . hash_hmac('sha256', $this->payload, $this->secret);
        return $this;
    }
}
