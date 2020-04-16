<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Ent\IDName;

use Bungle\Framework\Ent\IDName\NameAbleHighIDNameTranslator;
use Bungle\Framework\Entity\CommonTraits\NameAble;
use Bungle\Framework\Entity\CommonTraits\NameAbleInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use stdClass;

class NameAbleHighIDNameTranslatorTest extends MockeryTestCase
{
    public function testSupports()
    {
        $dm = Mockery::mock(DocumentManager::class);
        $idName = new NameAbleHighIDNameTranslator($dm);

        self::assertFalse($idName->supports('tst', self::class, 123));
        self::assertFalse($idName->supports('foo', stdClass::class, 123));

        $entity = new Class implements NameAbleInterface {
            use NameAble;
        };

        self::assertTrue($idName->supports('ord', get_class($entity), 123));
    }
}
