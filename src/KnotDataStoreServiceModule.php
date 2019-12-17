<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotDataStoreService;

use KnotLib\DataStoreService\ConnectionService;
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
use KnotLib\DataStoreService\DataStoreComponentTrait;
use KnotLib\DataStoreService\TransactionService;
use KnotLib\DataStoreService\RepositoryService;
use KnotLib\DataStoreService\DataStoreStringTrait;

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
            $c['component://database'] = function(Container $c) {
                $db_dsn  = $this->getDatabaseDSN($c);
                $db_user = getenv('DB_USER') ? getenv('DB_USER') : '';
                $db_pass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
                return new Database($db_dsn, $db_user, $db_pass);
            };

            // components.storage.default factory
            $c['component://storage:default'] = function(Container $c){
                $conn = $this->getConnection($c);
                return new DatabaseStorage($conn);
            };

            // components.connection.default factory
            $c['component://connection:default'] = function(Container $c){
                $db = $this->getDatabase($c);
                return $db->connection();
            };

            //====================================
            // Arrays
            //====================================

            //====================================
            // Strings
            //====================================

            // string.database.driver factory
            $c['string://database/driver'] = function(Container $c) {
                $conn = $this->getConnection($c);
                return $conn->getDriverName();
            };

            // string.database.dsn factory
            $c['string://database/dsn'] = function() {
                $db_dsn  = getenv('DB_DSN');
                return $db_dsn ? $db_dsn : '';
            };

            //====================================
            // Services
            //====================================

            // service.repository factory
            $c['service://repository'] = function(){
                return new RepositoryService();
            };

            // service.transaction.default factory
            $c['service://transaction:default'] = function(Container $c){
                $conn = $this->getConnection($c);
                return new TransactionService($conn);
            };

            // service.connection.default factory
            $c['service://connection:default'] = function(Container $c){
                $conn = $this->getConnection($c);
                return new ConnectionService($conn);
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