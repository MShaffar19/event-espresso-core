<?php
use EventEspresso\core\services\commands\CommandBus;
use EventEspresso\core\services\commands\middleware\CapChecker;
use EventEspresso\tests\mocks\core\domain\services\capabilities\CapabilitiesCheckerMock;
use EventEspresso\tests\mocks\core\services\commands\MockCommand;
use EventEspresso\tests\mocks\core\services\commands\MockCommandHandler;
use EventEspresso\tests\mocks\core\services\commands\RequiresCapCheckMockCommand;

defined('EVENT_ESPRESSO_VERSION') || exit;



/**
 * Class CommandBusTest
 * Description
 *
 * @package       Event Espresso
 * @author        Brent Christensen
 * 
 * @group         CommandBus
 */
class CommandBusTest extends EE_UnitTestCase
{

    /**
     * @var CommandBus $command_bus
     */
    private $command_bus;



    public function setUp()
    {
        EE_Dependency_Map::register_dependencies(
            'EventEspresso\tests\mocks\core\services\commands\CommandHandlerManagerMock',
            array('EE_Registry' => EE_Dependency_Map::load_from_cache,)
        );
        // need to override the existing alias for the CommandHandlerManagerInterface
        // or else the REAL class will still get used
        EE_Dependency_Map::instance()->add_alias(
            'EventEspresso\core\services\commands\CommandHandlerManagerInterface',
            'EventEspresso\tests\mocks\core\services\commands\CommandHandlerManagerMock'
        );
        parent::setUp();
    }



    protected function setupCommandBus($middleware = array())
    {
        // cleared cached version of CommandBus, else there is no way we are setting up a new one
        EE_Registry::instance()->clear_cached_class('EventEspresso\core\services\commands\CommandBus');
        // confirm that it is gone
        $this->assertNull(EE_Registry::instance()->BUS);
        // NOW setup a Bus that uses our Mocked CommandHandlerManager
        $this->command_bus = EE_Registry::instance()->create(
            'EventEspresso\core\services\commands\CommandBus',
            array(
                EE_Registry::instance()->create(
                    'EventEspresso\tests\mocks\core\services\commands\CommandHandlerManagerMock'
                ),
                $middleware
            )
        );
        $this->assertInstanceOf(
            'EventEspresso\core\services\commands\CommandBus',
            $this->command_bus
        );
    }



    public function testGetCommandHandlerManager()
    {
        $this->setupCommandBus();
        $this->assertInstanceOf(
            'EventEspresso\tests\mocks\core\services\commands\CommandHandlerManagerMock',
            $this->command_bus->getCommandHandlerManager()
        );
    }



    public function testExecute()
    {
        $this->setupCommandBus();
        // final results we want to see from CommandHandler
        $you_did_it = 'you done gone an did it now';
        // create CommandHandler and set the results
        $command_handler = new MockCommandHandler();
        $command_handler->results = $you_did_it;
        // the Command we want to use
        $command_fqcn = 'EventEspresso\tests\mocks\core\services\commands\MockCommand';
        $command_handler_manager = $this->command_bus->getCommandHandlerManager();
        // associate CommandHandler with the above Command
        $command_handler_manager->addCommandHandler(
            $command_handler,
            $command_fqcn
        );
        // execute Command and get results
        $results = $this->command_bus->execute(new MockCommand());
        $this->assertEquals($you_did_it, $results);
    }



    public function testExecuteWithPassingCapCheck()
    {
        // add CapChecker middleware that uses Mock that always returns true
        $this->setupCommandBus(
            array(new CapChecker(new CapabilitiesCheckerMock()))
        );
        // final results we want to see from CommandHandler
        $passed = 'this command passed its cap check';
        // create CommandHandler and set the results
        $command_handler = new MockCommandHandler();
        $command_handler->results = $passed;
        // the Command we want to use
        $command_fqcn = 'EventEspresso\tests\mocks\core\services\commands\RequiresCapCheckMockCommand';
        $command_handler_manager = $this->command_bus->getCommandHandlerManager();
        // associate CommandHandler with the above Command (note that class names don't match, but that's ok)
        $command_handler_manager->addCommandHandler(
            $command_handler,
            $command_fqcn
        );
        $results = $this->command_bus->execute(new RequiresCapCheckMockCommand());
        $this->assertEquals($passed, $results);
    }



    public function testExecuteWithFailingCapCheck()
    {
        $capabilities_checker = new CapabilitiesCheckerMock();
        // now change the CapabilitiesChecker so that ALL cap checks fail
        $capabilities_checker->cap_check_passes = false;
        // add CapChecker middleware that uses Mock that always returns true
        $this->setupCommandBus(
            array(new CapChecker($capabilities_checker))
        );
        // final results we want to see from CommandHandler
        $passed = 'this command should NOT pass its cap check';
        // create CommandHandler and set the results
        $command_handler = new MockCommandHandler();
        $command_handler->results = $passed;
        // the Command we want to use
        $command_fqcn = 'EventEspresso\tests\mocks\core\services\commands\RequiresCapCheckMockCommand';
        $command_handler_manager = $this->command_bus->getCommandHandlerManager();
        // associate CommandHandler with the above Command
        // note that the class names don't match, but that's ok,
        // because this Mock doesn't really resolve classnames and instantiate objects,
        // it just
        $command_handler_manager->addCommandHandler(
            $command_handler,
            $command_fqcn
        );
        $this->setExpectedException('EventEspresso\core\exceptions\InsufficientPermissionsException');
        $this->command_bus->execute(new RequiresCapCheckMockCommand());
        $this->fail('InsufficientPermissionsException should have been thrown');
    }



}
// End of file CommandBusTest.php
// Location: testcases/core/services/commands/CommandBusTest.php
