<?php
declare(strict_types=1);

namespace KnotModule\KnotDataStoreService\Test;

use KnotLib\DataStore\Storage\Database\Database;
use KnotLib\DataStore\Storage\Database\DatabaseConnection;
use KnotLib\DataStore\Storage\Database\DatabaseStorage;
use KnotLib\DataStoreService\ConnectionService;
use PHPUnit\Framework\TestCase;

use KnotLib\Kernel\Module\Components;
use KnotLib\DataStoreService\DI;
use KnotLib\DataStoreService\TransactionService;
use KnotLib\DataStoreService\RepositoryService;

use KnotPhp\Module\KnotDataStoreService\KnotDataStoreServiceModule;
use KnotPhp\Module\KnotDi\KnotDiModule;

final class KnotDataStoreServiceModuleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        putenv('DB_DSN=sqlite::memory:');
    }

    public function testRequiredComponents()
    {
        $this->assertEquals([
            Components::DI,
            Components::EVENTSTREAM,
        ],
        KnotDataStoreServiceModule::requiredComponents());
    }
    public function testDeclareComponentType()
    {
        $this->assertEquals(Components::MODULE, KnotDataStoreServiceModule::declareComponentType());
    }

    /**
     * @throws
     */
    public function testInstall()
    {
        $app = new TestApplication(new TestFileSystem());

        $app->installModules([
            KnotDiModule::class,
            KnotDataStoreServiceModule::class,
        ]);

        $di = $app->di();

        $this->assertNotNull($di);

        $this->assertInstanceOf(Database::class, $di[DI::URI_COMPONENT_DATABASE]);
        $this->assertInstanceOf(DatabaseStorage::class, $di[DI::uri(DI::URI_COMPONENT_STORAGE,'default')]);
        $this->assertInstanceOf(DatabaseConnection::class, $di[DI::uri(DI::URI_COMPONENT_CONNECTION,'default')]);

        $this->assertEquals('sqlite', $di[DI::URI_STRING_DB_DRIVER]);
        $this->assertEquals('sqlite::memory:', $di[DI::URI_STRING_DB_DSN]);

        $this->assertInstanceOf(TransactionService::class, $di[DI::uri(DI::URI_SERVICE_TRANSACTION,'default')]);
        $this->assertInstanceOf(ConnectionService::class, $di[DI::uri(DI::URI_SERVICE_CONNECTION,'default')]);
        $this->assertInstanceOf(RepositoryService::class, $di[DI::URI_SERVICE_REPOSITORY]);
    }
}