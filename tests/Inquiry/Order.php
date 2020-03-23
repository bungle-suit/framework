<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Inquiry;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
class Order
{
    /**
     * @ODM\Id()
     */
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }
}
