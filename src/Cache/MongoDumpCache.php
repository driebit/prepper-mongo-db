<?php

namespace Driebit\Prepper\MongoDb\Cache;

use Doctrine\ODM\MongoDB\DocumentManager;
use Driebit\DbBackup\MongoDumpBackup;
use Driebit\Prepper\Cache\AbstractDoctrineCache;
use Driebit\Prepper\Cache\Store\StoreInterface;
use Driebit\Prepper\Exception\BackupNotFoundException;
use Driebit\Prepper\Exception\BackupOutOfDateException;
use Driebit\Prepper\Fixture\FixtureSet;

class MongoDumpCache extends AbstractDoctrineCache
{
    private $backup;

    public function __construct(
        DocumentManager $objectManager,
        StoreInterface $store,
        MongoDumpBackup $backup
    ) {
        parent::__construct($objectManager, $store);
        $this->backup = $backup;
    }
    
    public function store(FixtureSet $fixtures)
    {
        $key = $this->getCacheKey($fixtures);
        $filename = $this->store->getPath($key);
        $this->backup->backup($this->getDatabase(), $filename);
    }
    
    public function restore(FixtureSet $fixtures)
    {
        $key = $this->getCacheKey($fixtures);
        if (!$this->store->has($key)) {
            throw new BackupNotFoundException($key);
        }
        
        $backup = $this->store->get($key);
        if ($backup->getCreated() < $fixtures->getLastModified()) {
            throw new BackupOutOfDateException($key);
        }
        
        $this->backup->restore(
            $this->getDatabase(),
            $backup->getFilename(),
            array('drop' => true)
        );
    }
    
    private function getDatabase()
    {
        $config = $this->objectManager->getConnection()->getConfiguration();
        
        return $config->getDefaultDB();
    }
}
