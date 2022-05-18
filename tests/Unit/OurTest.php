<?php

declare(strict_types=1);

namespace YaPro\SymfonySerializerUnderstanding\Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\FormErrorNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\MimeMessageNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
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

        // https://github.com/symfony/symfony/issues/35554 so we need to implement the
        // https://github.com/symfony/framework-bundle/blob/5.4/Resources/config/serializer.php

        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();

        $LoaderChain = new LoaderChain([]);
        $ClassMetadataFactory = new ClassMetadataFactory($LoaderChain);
        $ClassDiscriminatorFromClassMetadata = new ClassDiscriminatorFromClassMetadata($ClassMetadataFactory);
        $MetadataAwareNameConverter = new MetadataAwareNameConverter($ClassMetadataFactory);
        // $SerializerExtractor = new SerializerExtractor($ClassMetadataFactory);
        $ConstraintViolationListNormalizer = new ConstraintViolationListNormalizer([], $MetadataAwareNameConverter);
        $PropertyInfoExtractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor, $reflectionExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor],
            [$reflectionExtractor]
        );
        $PropertyNormalizer = new PropertyNormalizer($ClassMetadataFactory, $MetadataAwareNameConverter, $PropertyInfoExtractor, $ClassDiscriminatorFromClassMetadata);
        $PropertyAccessor = new PropertyAccessor();

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [
            new ArrayDenormalizer(),
            $ConstraintViolationListNormalizer,
            new DataUriNormalizer(new MimeTypes()),
            new DateIntervalNormalizer(),
            new DateTimeNormalizer(),
            new DateTimeZoneNormalizer(),
            new FormErrorNormalizer(),
            new JsonSerializableNormalizer(null, null),
            new MimeMessageNormalizer($PropertyNormalizer),
            new ObjectNormalizer($ClassMetadataFactory, $MetadataAwareNameConverter, $PropertyAccessor, $PropertyInfoExtractor, $ClassDiscriminatorFromClassMetadata),
            new ProblemNormalizer(true),
            $PropertyNormalizer,
            new UidNormalizer(),
            new UnwrappingDenormalizer($PropertyAccessor),
        ];

        self::$serializer = new Serializer($normalizers, $encoders);
    }

    private function assertJsons(string $result, string $expected)
    {
        // удаляем переносы строк и пробелы между именами полей и значениями, но не в значениях
        $resultAsArray = self::$jsonHelper->jsonDecode($result, true);
        $expectedAsArray = self::$jsonHelper->jsonDecode($expected, true);
        $this->assertSame(
            $expectedAsArray,
            $resultAsArray,
            'Original left: ' . $result . PHP_EOL . 'Original right: ' . $expected
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
        $this->assertJsons($result, $json = '
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

        // step 2: checking reverse serialization
        $kenFromJson = self::$serializer->deserialize($json, KenModel::class, 'json');
        $this->assertEquals($ken, $kenFromJson);

        // step 3: understanding json_decode
        $object = json_decode($json);
        $expected = new stdClass();
        $expected->wife = new stdClass();
        $expected->wife->id = 'Barbie';
        $expected->kids = [];
        $expected->kids[0] = new stdClass();
        $expected->kids[0]->id = 'Todd';
        $expected->kids[1] = new stdClass();
        $expected->kids[1]->id = 'Stacie';
        $expected->city = 'Moscow';
        $expected->id = 'Ken';
        $this->assertEquals($expected, $object);

        // step 4: understanding denormalize (denormalize из json завершается ошибкой, поэтому применяют deserialize)
        $kenFromObject = self::$serializer->denormalize($object, KenModel::class);
        $this->assertEquals($ken, $kenFromObject);

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

    // для обработки цикличной зависимости, в Serializer-е есть 2 способа:
    public function testCircularReference()
    {
        $object = new BarbieModel('Barbie');
        $subObject = new KenModel('Ken', $object, [], 'Moscow');
        $object->setHusband($subObject);

        $result = self::$serializer->serialize($object, 'json', [
            // AbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 2,
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
        ]);
        $this->assertJsons($result, '
            {
                "husband": {
                    "wife": "Barbie",
                    "kids": [],
                    "city": "Moscow",
                    "id": "Ken"
                },
                "id":"Barbie"
            }
        ');

        $result = self::$serializer->serialize($object, 'json', [
            AbstractNormalizer::CALLBACKS => [
                'husband' => function ($attributeValue) { // , $object, $attribute, $format, $context
                    return $attributeValue->getId();
                },
            ],
        ]);
        $this->assertJsons($result, '
            {
                "husband":"Ken",
                "id":"Barbie"
            }
        ');
    }

    // todo написать тест на:
    // AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
    // AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function($attributeValue, $object, $attribute, $format, $context){
    //     return '$attributeValue';
    // },
}
