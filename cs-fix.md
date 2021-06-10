### PHP-CS-FIX 安装

1. `mkdir -p tools/php-cs-fixer`
2. `composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer`
3. `tools/php-cs-fixer/vendor/bin/php-cs-fixer fix FilePath --config=.php_cs.php`