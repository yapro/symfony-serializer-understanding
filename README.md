# Понимая Symfony Serializer Component

Так вышло, что документация Symfony Serializer Component в некоторых местах не является достаточно детальной, поэтому
было решено рассмотреть основные ситуации в файле [tests/Unit/OurTest.php](tests/Unit/OurTest.php)

Например Symfony Serializer Component (в рамках serializer/Normalizer/MimeMessageNormalizer.php) от зависит symfony/mime
(а именно от Symfony\Component\Mime\Header\Headers), при этом ни в symfony/serializer-pack ни в symfony/serializer об
этом не упоминается, как по мне, так это странно.

Еще Symfony Serializer Component не имеет фабрики по созданию serializer-а в дефолтном состоянии, поэтому мы в
[tests/Unit/OurTest.php](tests/Unit/OurTest.php) создаем serializer так, как это делает [бандл](https://github.com/symfony/framework-bundle/blob/5.4/Resources/config/serializer.php).

![lib tests](https://github.com/yapro/symfony-serializer-understanding/actions/workflows/main.yml/badge.svg)

Build
```sh
docker build -t yapro/symfony-serializer-understanding:latest -f ./Dockerfile ./
```

Tests
```sh
docker run --rm --user=1000:1000 -v $(pwd):/app yapro/symfony-serializer-understanding:latest bash -c "cd /app \
  && composer install --optimize-autoloader --no-scripts --no-interaction \
  && vendor/bin/phpunit --testsuite=Unit"
```

Dev
```sh
docker run -it --rm --user=1000:1000 --add-host=host.docker.internal:host-gateway -v $(pwd):/app -w /app yapro/symfony-serializer-understanding:latest bash
composer install -o
```

Debug PHP:
```sh
PHP_IDE_CONFIG="serverName=common" \
XDEBUG_SESSION=common \
XDEBUG_MODE=debug \
XDEBUG_CONFIG="max_nesting_level=200 client_port=9003 client_host=host.docker.internal" \
vendor/bin/phpunit  --cache-result-file=/tmp/phpunit.cache -v --stderr --stop-on-incomplete --stop-on-defect \
--stop-on-failure --stop-on-warning --fail-on-warning --stop-on-risky --fail-on-risky --testsuite=Unit
```

Cs-Fixer: fix code
```sh
docker run --user=1000:1000 --rm -v $(pwd):/app -w /app yapro/symfony-serializer-understanding:latest ./php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php -v --using-cache=no --allow-risky=yes
```

PhpMd: update rules
```shell
docker run --user=1000:1000 --rm -v $(pwd):/app -w /app yapro/symfony-serializer-understanding:latest ./phpmd.phar . text phpmd.xml --exclude .github/workflows,vendor --strict --generate-baseline
```

### For myself:

Альтернатива 1: https://thomas.jarrand.fr/blog/serialization/
Альтернатива 2: JsonSerializable
- https://github.com/tarantool-php/mapper/blob/master/src/Entity.php#L72
- https://symfony.com/doc/current/serializer.html
- https://symfony.com/doc/current/components/serializer.html
- https://symfony.com/blog/new-in-symfony-4-1-serializer-improvements

Copyrights © YaPro.Ru
