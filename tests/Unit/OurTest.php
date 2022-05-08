<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use YaPro\Helper\JsonHelper;
use YaPro\SymfonySerializerUnderstanding\Tests\Model\BarbieModel;
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

    private function assertJsons(string $left, string $right)
    {
        // удаляем переносы строк и пробелы между именами полей и значениями, но не в значениях
        $leftAsArray = self::$jsonHelper->jsonDecode($left, true);
        $rightAsArray = self::$jsonHelper->jsonDecode($right, true);
        $this->assertSame(
            $leftAsArray,
            $rightAsArray,
            'Original left: ' . $left . PHP_EOL . 'Original right: ' . $right
        );
    }

    public function testFamily()
    {
        $kids = [
            new DollModel('Todd'),
            new DollModel('Stacie'),
        ];
        $wife = new DollModel('Barbie');
        $ken = new KenModel('Ken', $wife, $kids, 'Moscow');
        $result = self::$serializer->serialize($ken, 'json');
        $this->assertJsons($result, '
            {
                "wife": {
                    "id": "Barbie"
                },
                "kids": [
                    {
                        "id": "Todd"
                    },
                    {
                        "id": "Stacie"
                    }
                ],
                "city": "Moscow",
                "id": "Ken"
            }
        ');

        return $ken;
    }

    /**
     * https://symfony.com/doc/current/components/serializer.html#ignoring-attributes
     *
     * @depends testFamily
     */
    public function testIgnoreAttribute(KenModel $ken)
    {
        $result = self::$serializer->serialize($ken, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['city', 'kids'],
            // AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            // AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 2,
            // AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function($attributeValue, $object, $attribute, $format, $context){
            //     return '$attributeValue';
            // },
        ]);
        $this->assertJsons($result, '
            {
                "wife": {
                    "id": "Barbie"
                },
                "id": "Ken"
            }
        ');
    }

    public function testCircularReference()
    {
        $barbie = new BarbieModel('Barbie');
        $ken = new KenModel('Ken', $barbie, [], 'Doe', 'Moscow');
        $barbie->setHusband($ken);
        $relationHandler = function ($attributeValue) { // , $object, $attribute, $format, $context
            return $attributeValue->getId();
        };
        $result = self::$serializer->serialize($barbie, 'json', [
                AbstractObjectNormalizer::CALLBACKS => [
                    'husband' => $relationHandler,
                ],
                // AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function($object) {
                //     return $object->getId();
                // },
            ]);
        $this->assertJsons($result, '
            {
                "husband":"Ken",
                "id":"Barbie"
            }
        ');
    }
}
