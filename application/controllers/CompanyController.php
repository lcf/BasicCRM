<?php
class CompanyController extends Zend_Controller_Action
{
    public function register2Action() // TODO: just modify the previous function.
    {
        if ($this->_request->isPost()) {
            try {
                $companyService = ServiceLocator::getCompanyService();
                $companyService->registerCompany(
                    $this->_getParam('subscription-plan'),
                    $this->_getParam('name'),
                    $this->_getParam('admin-name'),
                    $this->_getParam('admin-email'),
                    $this->_getParam('password'),
                    $this->_getParam('confirm-password')
                );
                $this->_redirect('/company/register-success');
            } catch (DomainException $exception) {
                $this->view->assign($this->_getAllParams());
                $this->view->assign('error', $exception->getMessage());
            }
        }
        $this->render('register');
    }

    public function registerAction()
    {
        if ($this->_request->isPost()) {
            $companyService = ServiceLocator::getCompanyService();
            $companyService->registerCompany(
                $this->_getParam('subscription-plan'),
                $this->_getParam('name'),
                $this->_getParam('admin-name'),
                $this->_getParam('admin-email'),
                $this->_getParam('password'),
                $this->_getParam('confirm-password')
            );
            $this->_redirect('/company/register-success');
        }
    }

    public function registerSuccessAction()
    {
        // just view here
    }
}
