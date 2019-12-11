<?php
declare(strict_types=1);

namespace KnotModule\KnotDataStoreService\Test;

use PHPUnit\Framework\TestCase;
use KnotLib\Kernel\Module\Components;
use KnotModule\KnotDataStoreService\KnotDataStoreServiceModule;
use KnotModule\KnotDi\KnotDiModule;
use KnotLib\DataStore\Service\DI;
use KnotLib\DataStore\Service\TransactionService;
use KnotLib\DataStore\Service\RepositoryService;

final class KnotDataStoreServiceModuleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        putenv('DB_DSN=dummy');
    }

    public function testRequiredComponents()
    {
        $this->assertEquals([
            Components::DI,
            Components::LOGGER,
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

        $this->assertInstanceOf(TransactionService::class, $di[DI::SERVICE_TRANSACTION_DEFAULT]);
        $this->assertInstanceOf(RepositoryService::class, $di[DI::SERVICE_REPOSITORY]);
    }
}