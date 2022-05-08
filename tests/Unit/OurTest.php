<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use YaPro\Helper\JsonHelper;
use YaPro\SymfonySerializerUnderstanding\Tests\Model\DollModel;
use YaPro\SymfonySerializerUnderstanding\Tests\Model\KenModel;

class OurTest extends TestCase
{
    private static JsonHelper $jsonHelper;
    protected static SerializerInterface $serializer;

    public static function setUpBeforeClass(): void
    {
        self::$jsonHelper = new JsonHelper();

        // https://github.com/symfony/framework-bundle/blob/5.4/Resources/config/serializer.php
        // https://github.com/symfony/symfony/issues/35554
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        self::$serializer = new Serializer($normalizers, $encoders);
    }

    protected function setUp(): void
    {
    }

    private function assertJsons(string $left, string $right)
    {
        // удаляем переносы строк и пробелы между именами полей и значениями, но не в значениях
        $leftAsArray = self::$jsonHelper->jsonDecode($left, true);
        $rightAsArray = self::$jsonHelper->jsonDecode($right, true);
        $this->assertSame(
            $leftAsArray,
            $rightAsArray
        );
    }

    public function test1()
    {
        $kids = [
            new DollModel('Todd'),
            new DollModel('Stacie'),
        ];
        $wife = new DollModel('Barbie');
        $result = self::$serializer->serialize(new KenModel('Ken', $wife, $kids, 'Doe', 'Moscow'), 'json');
        $this->assertJsons($result, '
{
    "wife": {
        "name": "Barbie"
    },
    "kids": [
        {
            "name": "Todd"
        },
        {
            "name": "Stacie"
        }
    ],
    "surname": "Doe",
    "city": "Moscow",
    "name": "Ken"
}
        ');
    }
}
