<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use MongoDB\Client;
use MongoDB\Database;

const TEST_DB = 'test';

trait DBTestable
{
    private DocumentManager $dm;
    private Client $client;
    private Database $db;

    protected function setUp(): void
    {
        $this->client = new Client(
            'mongodb://localhost:27017', [],
            ['typeMap' => ['root' => 'array', 'document' => 'array']]
        );
        $config = new Configuration();
        $config->setDefaultDB(TEST_DB);
        $config->setHydratorDir('/tmp/mongo_db_test/hydrator');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $this->db = $this->client->selectDatabase(TEST_DB);

        $this->dm = DocumentManager::create($this->client, $config);
    }

}
