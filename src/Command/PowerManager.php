<?php

declare(strict_types=1);
/**
 * Application By zrone.
 *
 * @link     https://gitee.com/marksirl
 * @document https://gitee.com/marksirl
 * @contact  zrone<xujining415@gmail.com>
 */
namespace App\Command;

use Config\Config;
use ConstantUtil\Utils\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

/**
 * 目录权限管理.
 *
 * Class PowerManager
 */
class PowerManager extends Command
{
    // the name of the command (the part after "bin/grace")
    protected static $defaultName = 'power';

    protected $symbol = true;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('一键设置项目权限.')
            ->setHelp(
                <<<'HELP'
id_rsa 检查
检查项目目录权限
HELP
            );

        $this->addArgument(
            'args',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            <<<'DESC'
id_rsa [repository [domain]] 检查rsa是否有效，支持gitee、github和gitlab, 注意gitlab需要填写检测domain
check  [project]             检查目录权限
DESC
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = Arr::get($input->getArguments(), 'args');
        $io = new SymfonyStyle($input, $output);
        $this->checkDisabledFunc('system') && $io->error('system方法被禁用，请打开后尝试');

        switch (Arr::get($args, 0)) {
            case 'id_rsa':
                $this->rsaOption($args, $io);
                break;
            case 'check':
                $this->checkOption($args, $io);
                break;
        }

        return Command::SUCCESS;
    }

    private function rsaOption(array $args, SymfonyStyle $io): void
    {
        $repository = Arr::get($args, 1);

        switch ($repository) {
            case 'gitlab':
                if (! Arr::exists($args, 2)) {
                    $io->error('请填写gitlab域名');
                    exit();
                }
                    $domain = Arr::get($args, 2);
                    ! $this->domainCallback($domain) && $io->error('未验证的gitlab域名');

                break;
            case 'gitee':
                $domain = 'git@gitee.com';
                break;
            default:
                $domain = 'git@github.com';
                break;
        }

        $response = system("ssh -T {$domain}");
        if (is_numeric($response) && strpos($response, 'successfully authenticated') >= 0) {
            $io->success('rsa成功授权');
        } else {
            $io->warning('rsa授权失败，请检查rsa配置和权限配置是否正确');
        }
    }

    private function checkOption(array $args, SymfonyStyle $io): void
    {
        $path = Arr::get(Config::PROJECT, Arr::get($args, 1))['WEB_PATH'];

        $finder = new Finder();
        $finder->in($path)
            ->depth(0)
            ->ignoreUnreadableDirs(false)
            ->ignoreVCS(false)
            ->ignoreVCSIgnored(false)
            ->ignoreDotFiles(false);

        foreach ($finder as $file) {
            exec("ls -la {$file->getRealPath()}", $output);
            if ($file->isFile()) {
                $this->checkGrpAndOwn($output[0], $io, '');
            } else {
                foreach ($output as $item) {
                    if ($item == '.' || $item == '..') {
                        continue;
                    }
                    $this->checkGrpAndOwn($item, $io, $file->getRealPath() . '/');
                }
            }
        }
        if ($this->symbol) {
            $io->success('权限正常');
        }
    }

    private function checkGrpAndOwn(string $info, SymfonyStyle $io, string $prefix = '')
    {
        $fileArr = explode(' ', $info);
        $fileArr = array_merge(array_filter($fileArr, function ($row) {
            return ! empty(trim($row));
        }), []);

        if (count($fileArr) == 9) {
            $fileName = Arr::last($fileArr);

            if (($fileName != '.' && $fileName != '..') && ($fileArr[2] !== 'www' || $fileArr[3] !== 'www')) {
                $this->symbol = false;
                $io->writeln('<fg=#c0392b>权限验证失败' . $prefix . $fileName . ' ' . $fileArr[2] . ' ' . $fileArr[3] . '</>');
            }
        }
    }

    /**
     * 验证IP和域名.
     *
     * @return bool
     */
    private function domainCallback(string $domain)
    {
        return preg_match('/\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}(:\\d{2,5})?/', $domain, $ipMatches)
            || (preg_match('/[^\\s:\\\\]+(:\\d{2,5})?/', $domain, $urlMatches)
                && count($urlMatches) === 1);
    }

    private function checkDisabledFunc(string $func)
    {
        $disabled = explode(',', ini_get('disable_functions'));
        return in_array($func, $disabled);
    }
}
