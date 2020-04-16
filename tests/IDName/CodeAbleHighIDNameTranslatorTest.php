<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\IDName;

use Bungle\Framework\Entity\CommonTraits\CodeAble;
use Bungle\Framework\Entity\CommonTraits\CodeAbleInterface;
use Bungle\Framework\IDName\CodeAbleHighIDNameTranslator;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

class CodeAbleHighIDNameTranslatorTest extends TestCase
{
    public function testSupports()
    {
        $dm = Mockery::mock(DocumentManager::class);
        $idName = new CodeAbleHighIDNameTranslator($dm);

        self::assertFalse($idName->supports('tst', self::class, 123));
        self::assertFalse($idName->supports('foo', stdClass::class, 123));

        $entity = new Class implements CodeAbleInterface {
            use CodeAble;
        };

        self::assertTrue($idName->supports('ord', get_class($entity), 123));
    }
}
