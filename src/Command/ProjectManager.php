<?php

declare(strict_types=1);

/**
 * Gitee 自动化部署 by zrone<xujining2008@126.com>.
 *
 * @contact zrone
 */

namespace App\Command;

use Config\Config;
use ConstantUtil\Utils\Arr;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class ProjectManager extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'project';

    /** @var array $config */
    protected $config;

    /** @var InputInterface $input */
    protected $input;

    /** @var OutputInterface $output */
    protected $output;

    /**
     * ProjectManager constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->config = Config::PROJECT;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('部署项目管理.')
            ->setHelp(
                <<<HELP
添加、删除、修改部署项目配置
HELP
            );

        $this->addArgument(
            'args',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            <<<DESC
list [name] 查看配置
create      创建配置
remove name 删除配置
modify name 修改配置
DESC
            ,
            [
                'list',
                'all',
            ]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $args = Arr::get($input->getArguments(), 'args');

        $param = Arr::exists($args, 1) ? Arr::get($args, 1) : null;
        $io = new SymfonyStyle($input, $output);

        switch (Arr::get($args, 0)) {
            case 'list':
                if (empty($param)) {
                    $param = "all";
                }
                $this->listOperation($param, $io);
                break;

            case 'create':
                $this->createOperation($io);
                break;

            case 'remove':
                empty($param) && $io->error(
                    <<<ERROR
请指定移除配置名称 `bin/grace remove projectName`
ERROR
                );
                $this->removeOperation($param, $io);
                break;

            case 'modify':
                empty($param) && $io->error(
                    <<<ERROR
请指定待修改配置名称 `bin/grace modify projectName`
ERROR
                );
                $this->modifyOperation($param, $io);
                break;
        }

        return Command::SUCCESS;
    }

    /**
     * 创建配置
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    private function createOperation(SymfonyStyle $io): void
    {
        $projectName = $io->ask("配置名称:", null, function ($projectName) use ($io) {
            if (empty($projectName)) {
                $io->error("配置名称不能为空");
                exit();
            }
            return trim($projectName);
        });
        $projectSecret = $io->ask("SECRET:", null, function ($projectSecret) use ($io) {
            if (empty($projectSecret)) {
                $io->error("SECRET不能为空");
                exit();
            }
            return trim($projectSecret);
        });
        $projectWebPath = $io->ask("WEB_PATH:", null, function ($projectWebPath) use ($io) {
            if (empty($projectWebPath)) {
                $io->error("WEB_PATH不能为空");
                exit();
            }
            return trim($projectWebPath);
        });

        if (Arr::exists($this->config, $projectName)) {
            $io->error("项目名称 {$projectName} 已存在");
        } else {
            $this->config = Arr::merge($this->config, [
                $projectName => [
                    'SECRET'   => $projectSecret,
                    'WEB_PATH' => $projectWebPath,
                ],
            ]);

            $outArr = var_export($this->config, true);
            $configFile = <<<FILE
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
    public const PROJECT = {$outArr};
}

FILE;

            $filesystem = new Filesystem();
            $filesystem->dumpFile(__DIR__ . '/../../config/Config.php', $configFile);

            $io->success("{$projectName} 项目添加成功");
        }
    }

    /**
     * list 操作
     *
     * @param string $symbol
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    private function listOperation(string $symbol, SymfonyStyle $io): void
    {
        if ($symbol == 'all') {
            $io->title('项目配置列表');
            $response = [];
            foreach ($this->config as $type => $item) {
                $response[] = [
                    $type,
                    $item['SECRET'],
                    $item['WEB_PATH'],
                ];
            }
            $io->table(
                [
                    '项目名称',
                    'SECRET',
                    'WEB_PATH',
                ],
                $response
            );
        } elseif (Arr::exists($this->config, $symbol)) {
            $io->section("{$symbol} 项目配置");
            $item = Arr::get($this->config, $symbol);
            $io->horizontalTable(
                [
                    '项目名称',
                    'SECRET',
                    'WEB_PATH',
                ],
                [
                    [
                        $symbol,
                        $item['SECRET'],
                        $item['WEB_PATH'],
                    ],
                ]
            );
        } else {
            $io->error("{$symbol} 项目不存在");
        }
    }

    /**
     * 删除操作
     *
     * @param string $symbol
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    private function removeOperation(string $symbol, SymfonyStyle $io): void
    {
        if (Arr::exists($this->config, $symbol)) {
            Arr::forget($this->config, $symbol);

            $outArr = var_export($this->config, true);
            $configFile = <<<FILE
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
    public const PROJECT = {$outArr};
}

FILE;

            $filesystem = new Filesystem();
            $filesystem->dumpFile(__DIR__ . '/../../config/Config.php', $configFile);

            $io->success("{$symbol} 项目删除成功");
        } else {
            $io->error("{$symbol} 项目不存在");
        }
    }

    /**
     * 修改操作
     *
     * @param string $symbol
     * @param \Symfony\Component\Console\Style\SymfonyStyle $io
     */
    private function modifyOperation(string $symbol, SymfonyStyle $io): void
    {
        if (Arr::exists($this->config, $symbol)) {
            $io->section("当前项目配置");
            $item = Arr::get($this->config, $symbol);
            $io->horizontalTable(
                [
                    '项目名称',
                    'SECRET',
                    'WEB_PATH',
                ],
                [
                    [
                        $symbol,
                        $item['SECRET'],
                        $item['WEB_PATH'],
                    ],
                ]
            );


            $projectName = $io->ask("配置名称:", $symbol, function ($projectName) {
                return trim($projectName);
            });
            $projectSecret = $io->ask("SECRET:", $item['SECRET'], function ($projectSecret) {
                return trim($projectSecret);
            });
            $projectWebPath = $io->ask("WEB_PATH:", $item['WEB_PATH'], function ($projectWebPath) {
                return trim($projectWebPath);
            });

            if ($symbol != $projectName && Arr::exists($this->config, $projectName)) {
                $io->error("项目名称 {$projectName} 已存在");
            } else {
                // 删除重建
                Arr::forget($this->config, $symbol);
                $this->config = Arr::merge($this->config, [
                    $projectName => [
                        'SECRET'   => $projectSecret,
                        'WEB_PATH' => $projectWebPath,
                    ],
                ]);

                $outArr = var_export($this->config, true);
                $configFile = <<<FILE
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
    public const PROJECT = {$outArr};
}

FILE;

                $filesystem = new Filesystem();
                $filesystem->dumpFile(__DIR__ . '/../../config/Config.php', $configFile);

                $io->success("{$symbol} 项目修改成功");
            }
        } else {
            $io->error("{$symbol} 项目不存在");
        }
    }
}
