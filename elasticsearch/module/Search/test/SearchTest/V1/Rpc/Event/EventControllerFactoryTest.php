<?php

namespace SearchTest\V1\Rpc\Event;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\Rpc\Event\EventController;
use Search\V1\Rpc\Event\EventControllerFactory;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Search\V1\Rpc\Event\EventControllerFactory
 */
class EventControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $elasticSearchMock = self::getMock(ElasticSearchService::class, [], [], '', false);

        $serviceLocatorMock = self::getMock(ServiceLocatorInterface::class, [], [], '', false);
        $serviceLocatorMock->expects(self::once())
            ->method('get')
            ->with(ElasticSearchService::class)
            ->willReturn($elasticSearchMock);

        $controllersMock = self::getMock(ControllerManager::class, [], [], '', false);
        $controllersMock->expects(self::once())
            ->method('getServiceLocator')
            ->willReturn($serviceLocatorMock);

        $factory = new EventControllerFactory();

        $controller = $factory->__invoke($controllersMock);
        self::assertInstanceOf(EventController::class, $controller);
    }
}