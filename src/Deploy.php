<?php

declare(strict_types=1);
/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */

namespace App;

use Config\Config;
use ConstantUtil\Utils\Arr;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * 部署发布.
 *
 * Class Deploy
 */
class Deploy
{
    public $branch;

    public $webPath;

    /** @var Finder $finder */
    public $finder;

    /** @var bool $isAllowDeploy */
    public $isAllowDeploy = false;

    /** @var array $deployConfig */
    public $deployConfig;

    /** @var string $package */
    public $package;

    /**
     * Deploy constructor.
     */
    public function __construct(string $ref, string $package, array $config)
    {
        if (preg_match('/refs\\/heads\\/(.*)/', $ref, $matches)) {
            $this->branch = $matches[1];
        }
        $this->finder = new Finder();
        $this->webPath = $config['WEB_PATH'];
        $this->package = $package;
    }

    public static function build(string $ref, string $package, array $config): self
    {
        return new static($ref, $package, $config);
    }

    public function process(): self
    {
        $pullFile = SHELL_PATH . $this->package . '-pull.sh';
        $filesystem = new Filesystem();
        // 脚本存在删除重建
        $filesystem->exists($pullFile) && $filesystem->remove($pullFile);
        $pullText = <<<SHELL
#!/bin/bash

webpath=$this->webPath;
cd \${webpath};
# pull 脚本
git pull origin {$this->branch} 2>&1;
SHELL;

        $filesystem->dumpFile($pullFile, $pullText);
        $filesystem->chmod($pullFile, 0777);
        // 更新文件
        $code = system("sh {$pullFile}");

        if ($code != false) {
            $this->finder->in($this->webPath)->name('deploy-ci.yml')->depth(0)->files();

            foreach ($this->finder as $file) {
                $parseConfig = Yaml::parseFile($this->webPath . DIRECTORY_SEPARATOR . $file->getFilename());
                break;
            }

            $this->deployConfig = Arr::get($parseConfig, Arr::get($parseConfig, 'stage'));
            if (in_array($this->branch, Arr::get($this->deployConfig, 'only')) &&
                Arr::exists($this->deployConfig, 'script')) {
                $this->isAllowDeploy = true;
            }
        }
        return $this;
    }

    public function run() : bool
    {
        $logger = new Logger();
        if ($this->isAllowDeploy) {
            $shellFile = SHELL_PATH . $this->package . '-deploy.sh';

            $filesystem = new Filesystem();
            // 脚本存在删除重建
            $filesystem->exists($shellFile) && $filesystem->remove($shellFile);

            $shellText = <<<SHELL
#!/bin/bash

# 部署
webpath=$this->webPath;

# 配置shell脚本

SHELL;

            foreach (Arr::get($this->deployConfig, 'script') as $cmd) {
                $shellText .= $cmd . ';' . PHP_EOL;
            }

            $filesystem->dumpFile($shellFile, $shellText);
            $filesystem->chmod($shellFile, 0777);

            echo '开始部署...' . PHP_EOL;

            $code = system("sh {$shellFile}");

            if ($code != false) {
                echo '部署成功' . PHP_EOL;
                $logger(LoggerTypeEnum::HOOK)->alert('部署成功');
            } else {
                echo '部署脚本执行异常，请检查!!!' . PHP_EOL;
                $logger(LoggerTypeEnum::HOOK)->alert('部署脚本执行异常，请检查。');
            }
            return $code == 0;
        } else {
            $logger(LoggerTypeEnum::INFO)->info('未验证的分支取消部署');
        }

        return false;
    }
}
