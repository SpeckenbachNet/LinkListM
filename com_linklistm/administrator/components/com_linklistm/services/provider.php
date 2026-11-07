<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory as ComponentDispatcherFactoryServiceProvider;
use Joomla\CMS\Extension\Service\Provider\MVCFactory as MVCFactoryServiceProvider;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        // Register both namespaces explicitly; the dynamic namespace map is not reliable on every host
        $composerLoader = require JPATH_LIBRARIES . '/vendor/autoload.php';
        $composerLoader->addPsr4(
            'SpeckenbachNet\\Component\\LinkListM\\Administrator\\',
            __DIR__ . '/../src/',
            true
        );
        $composerLoader->addPsr4(
            'SpeckenbachNet\\Component\\LinkListM\\Site\\',
            JPATH_SITE . '/components/com_linklistm/src/',
            true
        );

        $container->registerServiceProvider(
            new MVCFactoryServiceProvider('\\SpeckenbachNet\\Component\\LinkListM')
        );

        $container->registerServiceProvider(
            new ComponentDispatcherFactoryServiceProvider('\\SpeckenbachNet\\Component\\LinkListM')
        );

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                // MVCComponent: the content plugin needs the component's MVC factory
                $component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
