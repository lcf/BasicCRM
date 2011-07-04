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

    public function __construct(\Zend_Config $config)
    {
        $transportClass = $config->get('transportClass');
        $options = $config->get('options');
        if ($transportClass == '\Zend_Mail_Transport_Smtp') {
            $sender = new \Zend_Mail_Transport_Smtp($options->get('host'), $options->toArray());
        } else {
            $sender = new $transportClass($options->toArray());
        }
        $builder = new \Zend_View(array('scriptPath' => APPLICATION_PATH . '/templates'));
        $this->sender = $sender;
        $this->builder = $builder;
        $this->fromEmail = $config->get('fromEmail');
        $this->fromName = $config->get('fromName');
    }

    public function registrationConfirmation(\Domain\Company $company)
    {
        $admin = $company->getAdmin();
        return $this->mail(
            'BasicCRM registration confirmation',
            'company/registration-confirmation',
            $admin->getEmail(),
            $admin->getName(),
            array('company' => $company)
        );
    }

    protected function mail($subject, $template, $toEmail, $toName, $parameters = array())
    {
        $mail = new Mail();
        $this->builder->assign($parameters);
        // You may wish to refactor the following try & catch blocks to utilizing is_readable()
        $atLeastOnePartRendered = false;
        try {
            $mail->setBodyHtml($this->builder->render($template . '.html'));
            $atLeastOnePartRendered = true;
        } catch (\Zend_View_Exception $exception) {}
        try {
            $mail->setBodyText($this->builder->render($template . '.txt'));
            $atLeastOnePartRendered = true;
        } catch (\Zend_View_Exception $exception) {}
        if (!$atLeastOnePartRendered) {
            throw new \RuntimeException('No templates found for ' . $template);
        }
        $mail->setSubject($subject);
        $mail->addTo($toEmail, $toName);
        $mail->setFrom($this->fromEmail, $this->fromName);
        $this->sender->send($mail);
    }
}
