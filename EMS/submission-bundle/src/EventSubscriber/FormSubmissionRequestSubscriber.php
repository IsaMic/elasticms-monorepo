<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\EventSubscriber;

use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class FormSubmissionRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private FormSubmissionRepository $formSubmissionRepository)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $formSubmissionId = $request->get('formSubmissionId');

        if (null === $formSubmissionId) {
            return;
        }

        $formSubmission = $this->formSubmissionRepository->findById($formSubmissionId);

        if (null === $formSubmission) {
            throw new NotFoundHttpException(\sprintf('Form submission with id %s not found', $formSubmissionId));
        }

        $request->attributes->set('formSubmission', $formSubmission);
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
