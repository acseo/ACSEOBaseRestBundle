<?php
namespace Acseo\Bundle\BaseRestBundle\Listener;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class BacktraceLoggerListener
{
    private $_logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->_logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->_logger->addError($event->getException());
    }
}
