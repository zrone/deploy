<?php

declare(strict_types=1);
/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */

namespace Config;

class Config
{
    public const PROJECT = [
        // bf 需要对应 hook url 的 package
        'bf' => [
            'SECRET'   => '秘钥', // 与WebHooks 签名密钥保持一直
            'WEB_PATH' => '/www/wwwroot/project', // Web项目工作路径
        ],
    ];
}
