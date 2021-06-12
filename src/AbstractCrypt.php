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

use Entity\CryptDataConfig;

abstract class AbstractCrypt implements CryptInterface
{
    public function __construct(CryptDataConfig $headerPreValidatorData)
    {
    }

    public static function build(CryptDataConfig $headerPreValidatorData): CryptInterface
    {
        switch ($headerPreValidatorData->symbol) {
            case RepositoryEnum::GITEE:
                $instance = new GiteeCrypt($headerPreValidatorData);
                break;
            case RepositoryEnum::GITLAB:
                $instance = new GitlabCrypt($headerPreValidatorData);
                break;
            default:
                $instance = new GithubCrypt($headerPreValidatorData);
                break;
        }

        return $instance;
    }

    /**
     * 验证签名.
     */
    public function compare(): bool
    {
        return $this->sign === $this->token;
    }
}
