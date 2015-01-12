<?php

namespace Driebit\Prepper\MongoDb\Resetter;

use Doctrine\ODM\MongoDB\DocumentManager;
use Driebit\Prepper\Resetter\ResetterInterface;

class MongoDbResetter implements ResetterInterface
{
    private $documentManager;
    
    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }
    
    public function reset()
    {
        $schemaManager = $this->documentManager->getSchemaManager();
        $schemaManager->dropDatabases();
    }
}
