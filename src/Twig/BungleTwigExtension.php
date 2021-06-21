<?php
declare(strict_types=1);

namespace Bungle\Framework\Twig;

use Bungle\Framework\Converter;
use Bungle\Framework\Ent\Code\UniqueName;
use Bungle\Framework\Ent\IDName\HighIDNameTranslator;
use Bungle\Framework\Ent\ObjectName;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BungleTwigExtension extends AbstractExtension
{
    private UniqueName $uidNames;

    public function __construct(
        private HighIDNameTranslator $highIDNameTranslator,
        private ObjectName $objectName,
        private SerializerInterface $serializer,
    ) {
        $this->uidNames = new UniqueName('__uid_');
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
                [$this, 'odmEncodeJson'],
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
            new TwigFunction('unique_id', [$this, 'uniqueId']),
        ];
    }

    /**
     * Return unique id. useful to generate random dom id.
     */
    public function uniqueId(): string
    {
        return $this->uidNames->next();
    }

    /**
     * Use symfony serializer to convert value to json.
     *
     * Named ODM is it can convert ODM query returned object/array,
     * but it may work with many other situations.
     * @param mixed $v
     */
    public function odmEncodeJson($v): string
    {
        return $this->serializer->serialize($v, 'json');
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
