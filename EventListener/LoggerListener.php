<?php

namespace DoctrineExtensions\SymfonyBundle\EventListener;

use Gedmo\Loggable\LoggableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Sets the username from the security context by listening on kernel.request
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class LoggerListener implements EventSubscriberInterface
{
    private $authorizationChecker;
    private $tokenStorage;
    private $loggableListener;

    public function __construct(LoggableListener $loggableListener, TokenStorageInterface $tokenStorage = null, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->loggableListener = $loggableListener;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @internal
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (null === $this->tokenStorage || null === $this->authorizationChecker) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null !== $token && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->loggableListener->setUsername($token);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }
}
