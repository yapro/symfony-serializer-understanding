<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use YaPro\SymfonySerializerUnderstanding\Tests\Model\DollModel;
use YaPro\SymfonySerializerUnderstanding\Tests\Model\KenModel;

class OurTest extends TestCase
{
    protected static SerializerInterface $serializer;

    public static function setUpBeforeClass(): void
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        self::$serializer = new Serializer($normalizers, $encoders);
    }

    protected function setUp(): void
    {
    }

    public function test1()
    {
        $kids = [
            new DollModel('Todd'),
            new DollModel('Stacie'),
        ];
        $wife = new DollModel('Barbie');
        $result = self::$serializer->serialize(new KenModel('Ken', $wife, $kids, 'Doe', 'Moscow'), 'json');
        $this->assertSame($result, '{"wife":{"name":"Barbie"},"kids":[{"name":"Todd"},{"name":"Stacie"}],"surname":"Doe","city":"Moscow","name":"Ken"}');
    }
}
