<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Code;

use Bungle\Framework\Code\CodeGenerator;
use DateTime;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use MongoDB\Client;
use PHP_CodeSniffer\Reports\Code;
use PHPUnit\Framework\TestCase;
use RangeException;

class CodeGeneratorTest extends TestCase
{
    const TEST_DB = 'test';

    private DocumentManager $dm;

    protected function setUp(): void
    {
        $client = new Client(
            'mongodb://localhost:27017', [],
            ['typeMap' => ['root' => 'array', 'document' => 'array']]
        );
        $config = new Configuration();
        $config->setDefaultDB(self::TEST_DB);
        $config->setHydratorDir('/tmp/mongo_db_test/hydrator');
        $config->setHydratorNamespace('Hydrators');

        $this->dm = DocumentManager::create($client, $config);
        $coll = $client->selectCollection(self::TEST_DB, CodeGenerator::ID_COLLECTION);
        $coll->drop();
    }

    public function testNextPrefixedCode()
    {
        $gen = new CodeGenerator($this->dm);
        self::assertEquals('foo001', $gen->nextPrefixedCode('foo', 3));
        self::assertEquals('bar1', $gen->nextPrefixedCode('bar', 1));
        self::assertEquals('foo002', $gen->nextPrefixedCode('foo', 3));
        self::assertEquals('foo003', $gen->nextPrefixedCode('foo', 3));
    }

    public function testNextPrefixedCodeOutOfRange(): void
    {
        $gen = new CodeGenerator($this->dm);
        for ($i = 0; $i < 9 ; $i++) {
            $gen->nextPrefixedCode('foo', 1);
        }

        $this->expectException(RangeException::class);
        $gen->nextPrefixedCode('foo', 1);
    }

    public function testCompactYearMonth(): void
    {
        $recs = [
            '2019-01-02' => '191',
            '2020-10-02' => '20X',
            '2020-11-02' => '20Y',
            '2020-12-02' => '20Z',
        ];
        foreach ($recs as $sd => $exp) {
            $d = new DateTime($sd);
            self::assertEquals($exp, CodeGenerator::compactYearMonth($d));
        }
    }
}
