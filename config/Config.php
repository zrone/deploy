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
            'SECRET'   => '35aad1f91e59e4388a5704b38b8793c988881f02', // 与WebHooks 签名密钥保持一直
            'WEB_PATH' => '/www/wwwroot/yoshop2.0', // Web项目工作路径
        ],
    ];
}
