<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * JsonLoginFactory creates services for JSON login authentication.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class JsonLoginFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('username_path', 'username');
        $this->addOption('password_path', 'password');
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'json-login';
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $provider = 'security.authentication.provider.dao.'.$id;
        $container
            ->setDefinition($provider, new DefinitionDecorator('security.authentication.provider.dao'))
            ->replaceArgument(0, new Reference($userProviderId))
            ->replaceArgument(1, new Reference('security.user_checker.'.$id))
            ->replaceArgument(2, $id)
        ;

        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId()
    {
        return 'security.authentication.listener.json';
    }

    /**
     * {@inheritdoc}
     */
    protected function isRememberMeAware($config)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = $this->getListenerId();
        $listener = new DefinitionDecorator($listenerId);
        $listener->replaceArgument(2, $id);
        $listener->replaceArgument(3, new Reference($this->createAuthenticationSuccessHandler($container, $id, $config)));
        $listener->replaceArgument(4, new Reference($this->createAuthenticationFailureHandler($container, $id, $config)));
        $listener->replaceArgument(5, array_intersect_key($config, $this->options));

        $listenerId .= '.'.$id;
        $container->setDefinition($listenerId, $listener);

        return $listenerId;
    }
}
