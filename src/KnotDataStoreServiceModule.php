<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotDataStoreService;

use Throwable;

use KnotLib\Di\Container;
use KnotLib\DataStore\Storage\Database\Database;
use KnotLib\DataStore\Storage\Database\DatabaseStorage;

use KnotLib\Kernel\Module\Components;
use KnotLib\Kernel\Exception\ModuleInstallationException;
use KnotLib\Kernel\Kernel\ApplicationInterface;
use KnotLib\Kernel\Module\ComponentModule;
use KnotLib\Kernel\EventStream\Events;
use KnotLib\Kernel\EventStream\Channels as EventChannels;
use KnotLib\DataStore\Service\DataStoreComponentTrait;
use KnotLib\DataStore\Service\TransactionService;
use KnotLib\DataStore\Service\DI;
use KnotLib\DataStore\Service\RepositoryService;
use KnotLib\DataStore\Service\DataStoreStringTrait;

final class KnotDataStoreServiceModule extends ComponentModule
{
    use DataStoreComponentTrait;
    use DataStoreStringTrait;

    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponents() : array
    {
        return [
            Components::DI,
            Components::LOGGER,
            Components::EVENTSTREAM,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return Components::MODULE;
    }

    /**
     * Install module
     *
     * @param ApplicationInterface $app
     *
     * @throws
     */
    public function install(ApplicationInterface $app)
    {
        try{
            $c = $app->di();

            //====================================
            // Components
            //====================================

            // components.database factory
            $c[DI::COMPONENT_DATABASE] = function(Container $c) {
                $db_dsn  = $this->getDatabaseDSN($c);
                $db_user = getenv('DB_USER') ? getenv('DB_USER') : '';
                $db_pass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
                return new Database($db_dsn, $db_user, $db_pass);
            };

            // components.storage.default factory
            $c[DI::COMPONENT_STORAGE_DEAULT] = function(Container $c){
                $conn = $this->getDefaultConnection($c);
                return new DatabaseStorage($conn);
            };

            // components.connection.default factory
            $c[DI::COMPONENT_CONNECTION_DEAULT] = function(Container $c){
                $db = $this->getDatabase($c);
                return $db->connection();
            };

            //====================================
            // Arrays
            //====================================

            //====================================
            // Strings
            //====================================

            // strings.db_driver factory
            $c[DI::STRING_DB_DRIVER] = function(Container $c) {
                $conn = $this->getDefaultConnection($c);
                return $conn->getDriverName();
            };

            // strings.db_dsn factory
            $c[DI::STRING_DB_DSN] = function() {
                $db_dsn  = getenv('DB_DSN');
                return $db_dsn ? $db_dsn : '';
            };

            //====================================
            // Services
            //====================================

            // services.repository factory
            $c[DI::SERVICE_REPOSITORY] = function(){
                return new RepositoryService();
            };

            // services.transaction.default factory
            $c[DI::SERVICE_TRANSACTION_DEFAULT] = function(Container $c){
                $conn = $this->getDefaultConnection($c);
                return new TransactionService($conn);
            };

            // fire event
            $app->eventstream()->channel(EventChannels::SYSTEM)->push(Events::MODULE_INSTALLED, $this);
        }
        catch(Throwable $e)
        {
            throw new ModuleInstallationException(self::class, $e->getMessage(), 0, $e);
        }
    }
}