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
 * gitlab 签名验证
 *
 * Class GitlabCrypt
 */
class GitlabCrypt extends AbstractCrypt
{
    public $secret;

    public $token;

    public $sign;

    public function __construct(CryptDataConfig $headerPreValidatorData)
    {
        parent::__construct($headerPreValidatorData);

        $this->secret = Arr::get($headerPreValidatorData->config, 'SECRET');
        $this->token = $headerPreValidatorData->headers->get('x-gitlab-token');
    }

    /**
     * 签名.
     */
    public function buildPrefixCryptSign(): CryptInterface
    {
        $this->sign = $this->secret;
        return $this;
    }
}
