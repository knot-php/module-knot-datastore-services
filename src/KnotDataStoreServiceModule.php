<?php
declare(strict_types=1);

namespace KnotPhp\Module\KnotDataStoreService;

use KnotLib\DataStoreService\ConnectionService;
use KnotLib\DataStoreService\DI;
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
use KnotLib\DataStoreService\Util\DataStoreComponentTrait;
use KnotLib\DataStoreService\Util\DataStoreStringTrait;
use KnotLib\DataStoreService\TransactionService;
use KnotLib\DataStoreService\RepositoryService;

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

            // component://database factory
            $c[DI::URI_COMPONENT_DATABASE] = function(Container $c) {
                $db_dsn  = $this->getDatabaseDSN($c);
                $db_user = getenv('DB_USER') ? getenv('DB_USER') : '';
                $db_pass = getenv('DB_PASS') ? getenv('DB_PASS') : '';
                return new Database($db_dsn, $db_user, $db_pass);
            };

            // component://storage:default factory
            $c[sprintf(DI::URI_COMPONENT_STORAGE,'default')] = function(Container $c){
                $conn = $this->getConnection($c);
                return new DatabaseStorage($conn);
            };

            // component://connection:default factory
            $c[sprintf(DI::URI_COMPONENT_CONNECTION,'default')] = function(Container $c){
                $db = $this->getDatabase($c);
                return $db->connection();
            };

            //====================================
            // Arrays
            //====================================

            //====================================
            // Strings
            //====================================

            // string://database/driver factory
            $c[DI::URI_STRING_DB_DRIVER] = function(Container $c) {
                $conn = $this->getConnection($c);
                return $conn->getDriverName();
            };

            // string://database/dsn factory
            $c[DI::URI_STRING_DB_DSN] = function() {
                $db_dsn  = getenv('DB_DSN');
                return $db_dsn ? $db_dsn : '';
            };

            //====================================
            // Services
            //====================================

            // service://repository factory
            $c[DI::URI_SERVICE_REPOSITORY] = function(){
                return new RepositoryService();
            };

            // service.transaction.default factory
            $c[sprintf(DI::URI_SERVICE_TRANSACTION,'default')] = function(Container $c){
                $conn = $this->getConnection($c);
                return new TransactionService($conn);
            };

            // service.connection.default factory
            $c[sprintf(DI::URI_SERVICE_CONNECTION,'default')] = function(Container $c){
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