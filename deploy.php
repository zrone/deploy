<?php

declare(strict_types=1);

/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */

use App\Deploy;
use App\GiteeCrypt;
use App\Logger;
use App\LoggerTypeEnum;
use Config\Config;
use ConstantUtil\Utils\Arr;
use Entity\CryptDataConfig;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

define("SHELL_PATH", __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'shell' . DIRECTORY_SEPARATOR);

require './vendor/autoload.php';
\Composer\Autoload\includeFile('./config/Config.php');
\Composer\Autoload\includeFile('./entity/CryptDataConfig.php');

set_time_limit(0);

$result = false;
$deployCode = false;
try {
    $content = file_get_contents('php://input');
    $request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $content);
    $request->headers = new HeaderBag(getallheaders());

    $package = $request->query->get('package', null);
    if (Arr::exists(Config::PROJECT, $package)) {
        $result = GiteeCrypt::build(new CryptDataConfig([
            'config'    => Arr::get(Config::PROJECT, $package),
            'timestamp' => $request->headers->get('x-gitee-timestamp'),
            'token'     => $request->headers->get('x-gitee-token'),
        ]))
            ->buildPrefixCryptSign()
            ->compare();

        $requestData = json_decode($request->getContent(), true);

        $result &&
        $deployCode = Deploy::build($requestData['ref'], $package, Arr::get(Config::PROJECT, $package))
            ->process()
            ->run();
    }
} catch (\Exception $exception) {
    $logger = new Logger();
    $logger(LoggerTypeEnum::ERROR)->error('请求异常', get_object_vars($exception));
}

$response = new Response($deployCode ? 'Enjoy it!' : 'You should to check deploy code!');
return $response->send();
