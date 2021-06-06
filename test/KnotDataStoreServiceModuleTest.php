<?php
declare(strict_types=1);

namespace knotphp\module\knotdatastoreservices\test;

use knotlib\datastoreservices\ConnectionService;
use knotlib\datastoreservices\DI;
use knotlib\datastoreservices\RepositoryService;
use knotlib\datastoreservices\TransactionService;
use knotphp\module\knotdatastoreservices\test\classes\TestApplication;
use knotphp\module\knotdatastoreservices\test\classes\TestFileSystem;
use PHPUnit\Framework\TestCase;

use knotlib\datastore\storage\database\Database;
use knotlib\datastore\storage\database\DatabaseConnection;
use knotlib\datastore\storage\database\DatabaseStorage;

use knotlib\kernel\module\ComponentTypes;

use knotphp\module\knotdatastoreservices\KnotDataStoreServiceModule;
use knotphp\module\knotdi\knotdimodule;

final class KnotDataStoreServiceModuleTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        putenv('DB_DSN=sqlite::memory:');
    }

    public function testRequiredComponentTypes()
    {
        $this->assertEquals([
            ComponentTypes::DI,
            ComponentTypes::EVENTSTREAM,
        ],
        KnotDataStoreServiceModule::requiredComponentTypes());
    }
    public function testDeclareComponentType()
    {
        $this->assertEquals(ComponentTypes::SERVICE, KnotDataStoreServiceModule::declareComponentType());
    }

    /**
     * @throws
     */
    public function testInstall()
    {
        $app = new TestApplication(new TestFileSystem());

        $app->installModule(KnotDiModule::class);
        $app->installModule(KnotDataStoreServiceModule::class);

        $di = $app->di();

        $this->assertNotNull($di);

        $this->assertInstanceOf(Database::class, $di[DI::URI_COMPONENT_DATABASE]);
        $this->assertInstanceOf(DatabaseStorage::class, $di['component://storage:default']);
        $this->assertInstanceOf(DatabaseConnection::class, $di['component://connection:default']);

        $this->assertEquals('sqlite', $di[DI::URI_STRING_DB_DRIVER]);
        $this->assertEquals('sqlite::memory:', $di[DI::URI_STRING_DB_DSN]);

        $this->assertInstanceOf(TransactionService::class, $di['service://transaction:default']);
        $this->assertInstanceOf(ConnectionService::class, $di['service://connection:default']);
        $this->assertInstanceOf(RepositoryService::class, $di['service://repository']);
    }
}