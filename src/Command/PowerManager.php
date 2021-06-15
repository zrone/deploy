<?php


namespace App\Command;


use Config\Config;
use ConstantUtil\Utils\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 目录权限管理
 *
 * Class PowerManager
 * @package App\Command
 */
class PowerManager extends Command
{
    // the name of the command (the part after "bin/grace")
    protected static $defaultName = 'power';

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
check  [project]             创建配置
DESC
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $args = Arr::get($input->getArguments(), 'args');
        $io = new SymfonyStyle($input, $output);
        $this->checkDisabledFunc('system') && $io->error("system方法被禁用，请打开后尝试");

        switch (Arr::get($args, 0)) {
            case 'id_rsa':
                $this->rsaOption($args, $io);
                break;
            case 'check':
                $project = Arr::get($args, 1);
                $io->info($project);
                break;
        }

        return Command::SUCCESS;
    }

    private function rsaOption(array $args, SymfonyStyle $io): void
    {
        $repository = Arr::get($args, 1);

        switch ($repository) {
            case 'gitlab':
                if(!Arr::exists($args, 2)) {
                    $io->error("请填写gitlab域名");
                    exit();
                } else {
                    $domain = Arr::get($args, 2);
                    !$this->domainCallback($domain) && $io->error("未验证的gitlab域名");
                }
                break;

            case 'gitee':
                $domain = 'git@gitee.com';
                break;

            default:
                $domain = 'git@github.com';
                break;
        }

        $response = system("ssh -T {$domain}");
        if (is_numeric($response) && strpos($response, "successfully authenticated") >= 0) {
            $io->success("rsa成功授权");
        } else {
            $io->warning("rsa授权失败，请检查rsa配置和权限配置是否正确");
        }
    }

    private function checkOption(array $args, SymfonyStyle $io): void {
        if(!Arr::exists($args, 2)) {
            $io->error("请填写项目名称");
            exit();
        }

        $project = Arr::exists($args, 2);
        $path = Arr::get(Config::PROJECT, Arr::exists($args, 2))['WEB_PATH'];

        $rootResponse = system("ls -la {$path} | awk '{printf \"%15s %6s %6s\n\", $9, $3, $4}'");
        $gitResponse = system("ls -la {$path}/.git | awk '{printf \"%15s %6s %6s\n\", $9, $3, $4}'");

        $io->success($rootResponse);
    }

    /**
     * 验证IP和域名
     *
     * @param string $domain
     * @return bool
     */
    private function domainCallback(string $domain)
    {
        return preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:\d{2,5})?/", $domain, $ipMatches) ||
            (preg_match("/[^\s:\\\]+(:\d{2,5})?/", $domain, $urlMatches) &&
                count($urlMatches) === 1);
    }

    private function checkDisabledFunc(string $func)
    {
        $disabled = explode(',', ini_get('disable_functions'));
        return in_array($func, $disabled);
    }
}