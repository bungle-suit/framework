<?php
declare(strict_types=1);

namespace Bungle\Framework\Twig;

use Bungle\Framework\Converter;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BungleTwigExtension extends AbstractExtension
{
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
        ];
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
}
