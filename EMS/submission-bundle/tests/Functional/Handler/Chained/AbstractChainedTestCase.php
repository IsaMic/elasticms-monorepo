<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Tests\Functional\Handler\Chained;

use EMS\FormBundle\FormConfig\FormConfig;
use EMS\FormBundle\FormConfig\SubmissionConfig;
use EMS\FormBundle\Submission\FormData;
use EMS\FormBundle\Submission\HandleRequest;
use EMS\FormBundle\Submission\HandleResponseCollector;
use EMS\SubmissionBundle\Tests\Functional\AbstractFunctionalTestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractChainedTestCase extends AbstractFunctionalTestCase
{
    private FormInterface $form;
    /** @var FormConfig */
    protected $formConfig;
    /** @var HandleResponseCollector */
    protected $responseCollector;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->formConfig = new FormConfig('1', 'nl', 'test_test');
        $this->form = $this->createForm();
        $this->responseCollector = new HandleResponseCollector();
    }

    protected function createRequest(string $handlerClass, string $endpoint, string $message): HandleRequest
    {
        $submissionConfig = new SubmissionConfig($handlerClass, $endpoint, $message);
        $formData = new FormData($this->formConfig, $this->form);

        return new HandleRequest($this->form, $this->formConfig, $formData, $this->responseCollector, $submissionConfig);
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function createForm(array $data = []): FormInterface
    {
        if (null == $data) {
            $data = [
                'first_name' => 'testFirstName',
                'last_name' => 'testLastName',
                'email' => 'user1@test.test',
                'info' => 'test chaining',
                'attachments' => [
                    new UploadedFile(__DIR__.'/../../fixtures/files/attachment.txt', 'attachment.txt', 'text/plain'),
                    new UploadedFile(__DIR__.'/../../fixtures/files/attachment2.txt', 'attachment2.txt', 'text/plain'),
                ],
            ];
        }

        return Forms::createFormFactoryBuilder()->getFormFactory()->createBuilder(FormType::class, $data, [])
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('info', TextType::class)
            ->add('email', EmailType::class)
            ->add('attachments', FileType::class, ['multiple' => true])
            ->getForm();
    }
}
