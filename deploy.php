<?php

declare(strict_types=1);
/**
 * Application By zrone.
 *
 * @link     https://gitee.com/marksirl
 * @document https://gitee.com/marksirl
 * @contact  zrone<xujining415@gmail.com>
 */
use App\AbstractCrypt;
use App\Deploy;
use App\Logger;
use App\LoggerTypeEnum;
use Config\Config;
use ConstantUtil\Utils\Arr;
use Entity\CryptDataConfig;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

define('SHELL_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'shell' . DIRECTORY_SEPARATOR);

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

    $requestData = json_decode($request->getContent(), true);
    // 独立package
    $package = $request->query->get('package', null);

    if (Arr::exists(Config::PROJECT, $package)) {
        $result = AbstractCrypt::build(new CryptDataConfig([
            'htmlUrl' => Arr::get($requestData, 'repository')['html_url'],
            'config' => Arr::get(Config::PROJECT, $package),
            'headers' => $request->headers,
            'payload' => $request->getContent(),
        ]))
            ->buildPrefixCryptSign()
            ->compare();

        // 兼容 github 单分支找不到refs
        $requestData['ref'] = Arr::exists($requestData, 'ref') ? Arr::get($requestData, 'ref') : 'refs/heads/master';

        $result
        && $deployCode = Deploy::build($requestData['ref'], $package, Arr::get(Config::PROJECT, $package))
            ->process()
            ->run();
    }
} catch (\Exception $exception) {
    $logger = new Logger();
    $logger(LoggerTypeEnum::ERROR)->error('请求异常', get_object_vars($exception));
}

$response = new Response($deployCode ? 'Enjoy it!' : 'You should to check deploy code!');
return $response->send();
