<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use SpeckenbachNet\Plugin\Content\Starratingm\Extension\StarRatingM;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new StarRatingM(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('content', 'starratingm')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};