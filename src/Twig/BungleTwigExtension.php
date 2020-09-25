<?php
declare(strict_types=1);

namespace Bungle\Framework\Twig;

use Bungle\Framework\Converter;
use Bungle\Framework\Ent\IDName\HighIDNameTranslator;
use Bungle\Framework\Ent\ObjectName;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BungleTwigExtension extends AbstractExtension
{
    private HighIDNameTranslator $highIDNameTranslator;
    private ObjectName $objectName;
    private static $uniqueId = 0;

    public function __construct(HighIDNameTranslator $highIDNameTranslator, ObjectName $objectName)
    {
        $this->highIDNameTranslator = $highIDNameTranslator;
        $this->objectName = $objectName;
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('bungle_format', Converter::class.'::format'),
            new TwigFilter(
                'odm_encode_json',
                self::class.'::odmEncodeJson',
                ['is_safe' => ['js']]
            ),
            new TwigFilter(
                'id_name',
                [$this, 'highIdName'],
            ),
            new TwigFilter('justify', Converter::class.'::justifyAlign'),
            new TwigFilter('object_name', [$this->objectName, 'getName']),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('unique_id', [self::class, 'uniqueId']),
        ];
    }

    /**
     * Return unique id. useful to generate random dom id.
     */
    public static function uniqueId(): string
    {
        return '__uid_'.(++self::$uniqueId);
    }

    /**
     * Use symfony serializer to convert value to json.
     *
     * Named ODM is it can convert ODM query returned object/array,
     * but it may work with many other situations.
     */
    public static function odmEncodeJson($v): string
    {
        $encoders = [new JsonEncoder(
            new JsonEncode([JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE]),
            new JsonDecode()
        )];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($v, 'json');
    }

    /**
     * Use HighIDNameTranslator convert id to name.
     *
     * @param int|string|null $v
     */
    public function highIdName($v, string $high): string
    {
        return $this->highIDNameTranslator->idToName($high, $v);
    }
}
