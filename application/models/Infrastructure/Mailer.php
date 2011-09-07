<?php
namespace Infrastructure;

use Zend_Mail as Mail;

class Mailer
{
    /**
     * @var \Zend_Mail_Transport_Abstract
     */
    protected $sender;

    /**
     * @var \Zend_View
     */
    protected $builder;

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var string
     */
    protected $fromName;

    public function __construct(\Zend_Mail_Transport_Abstract $sender, \Zend_View_Interface $builder, $defaultFromEmail, $defaultFromName)
    {
        $this->sender = $sender;
        $this->builder = $builder;
        $this->fromEmail = $defaultFromEmail;
        $this->fromName = $defaultFromName;
    }

    /**
     * Sends login instructions for a new user
     *
     * @param \Domain\User $user
     * @param string $password
     * @return void
     */
    public function newUserWelcome(\Domain\User $user, $password)
    {
        $this->mail(
            'Welcome to BasicCRM',
            'company/new-user-welcome',
            $user->getEmail(),
            $user->getName(),
            array('email' => $user->getEmail(),
                  'companyName' => $user->getCompany()->getName(),
                  'password' => $password)
        );
    }

    /**
     * Sends registration confirmation email to the administrator of the
     * given company
     *
     * @param \Domain\Company $company
     * @return void
     */
    public function registrationConfirmation(\Domain\Company $company)
    {
        $salt = \ServiceLocator::getDomainConfig()->get('confirmationCodeSalt');
        $admin = $company->getAdmin();
        $this->mail(
            'BasicCRM registration confirmation',
            'company/registration-confirmation',
            $admin->getEmail(),
            $admin->getName(),
            array('companyId' => $company->getId(),
                  'confirmationCode' => $company->getConfirmationCode($salt))
        );
    }

    /**
     * Renders given template, sets mail message parameters such as
     * to, from, subject
     * and sends using the builder.
     *
     * @throws \RuntimeException
     * @param $subject
     * @param $template
     * @param $toEmail
     * @param $toName
     * @param array $parameters
     * @return void
     */
    protected function mail($subject, $template, $toEmail, $toName, $parameters = array())
    {
        $mail = new Mail();
        // Passing the parameters to the Zend_View instance
        $this->builder->assign($parameters);
        // Here we try to render both .txt and .html files for the template
        // You may wish to refactor the following try & catch blocks to utilizing is_readable() instead
        $atLeastOnePartRendered = false;
        try {
            $mail->setBodyHtml($this->builder->render($template . '.html'));
            $atLeastOnePartRendered = true;
        } catch (\Zend_View_Exception $exception) {}
        try {
            $mail->setBodyText($this->builder->render($template . '.txt'));
            $atLeastOnePartRendered = true;
        } catch (\Zend_View_Exception $exception) {}
        // at least one email version must exist
        if (!$atLeastOnePartRendered) {
            throw new \RuntimeException('No templates found for ' . $template);
        }
        // setting email parameters
        $mail->setSubject($subject);
        $mail->addTo($toEmail, $toName);
        $mail->setFrom($this->fromEmail, $this->fromName);
        // sending
        $this->sender->send($mail);
    }
}
