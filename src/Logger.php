<?php

declare(strict_types=1);
/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */
namespace App;

use Carbon\Carbon;

class Logger
{
    public static $loggerInstance;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        if (! self::$loggerInstance instanceof \Monolog\Logger) {
            $logger = new \Monolog\Logger('demo');
            $logger->setTimezone(new \DateTimeZone('Asia/Shanghai'));

            self::$loggerInstance = $logger;
        }
    }

    public function __invoke(string $type): \Monolog\Logger
    {
        $loggerFileName = Carbon::today()->format('Y-m-d') . '-' . $type;
        self::$loggerInstance->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . "/../runtime/log/{$loggerFileName}.log"));

        return self::$loggerInstance;
    }
}
