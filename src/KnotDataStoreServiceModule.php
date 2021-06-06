<?php
declare(strict_types=1);

namespace knotphp\module\knotdatastoreservices;

use Throwable;

use knotlib\di\Container;
use knotlib\datastore\storage\database\Database;
use knotlib\datastore\storage\database\DatabaseStorage;
use knotlib\kernel\module\ModuleInterface;
use knotlib\kernel\module\ComponentTypes;
use knotlib\kernel\exception\ModuleInstallationException;
use knotlib\kernel\kernel\ApplicationInterface;
use knotlib\kernel\eventstream\Events;
use knotlib\kernel\eventstream\Channels as EventChannels;
use knotlib\datastoreservices\util\DataStoreComponentTrait;
use knotlib\datastoreservices\util\DataStoreStringTrait;
use knotlib\datastoreservices\ConnectionService;
use knotlib\datastoreservices\DI;
use knotlib\datastoreservices\RepositoryService;
use knotlib\datastoreservices\TransactionService;

final class KnotDataStoreServiceModule implements ModuleInterface
{
    use DataStoreComponentTrait;
    use DataStoreStringTrait;

    /**
     * Declare dependency on another modules
     *
     * @return array
     */
    public static function requiredModules() : array
    {
        return [];
    }

    /**
     * Declare dependent on components
     *
     * @return array
     */
    public static function requiredComponentTypes() : array
    {
        return [
            ComponentTypes::DI,
            ComponentTypes::EVENTSTREAM,
        ];
    }

    /**
     * Declare component type of this module
     *
     * @return string
     */
    public static function declareComponentType() : string
    {
        return ComponentTypes::SERVICE;
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
            // ComponentTypes
            //====================================

            // component://database factory
            $c[DI::URI_COMPONENT_DATABASE] = function(Container $c) {
                $db_dsn  = $this->getDatabaseDSN($c);
                $db_user = getenv('DB_USER') ?: '';
                $db_pass = getenv('DB_PASS') ?: '';
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
                return $db_dsn ?: '';
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
        catch(Throwable $ex)
        {
            throw new ModuleInstallationException(self::class, $ex->getMessage(), $ex);
        }
    }
}