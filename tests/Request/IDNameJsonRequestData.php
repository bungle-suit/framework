<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Request;

use Bungle\Framework\Request\JsonRequestDataInterface;

class IDNameJsonRequestData implements JsonRequestDataInterface
{
    private string $name = '';
    /** @var string[] */
    private array $ids = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    /**
     * @param string[] $ids
     */
    public function setIds(array $ids): void
    {
        $this->ids = $ids;
    }
}
