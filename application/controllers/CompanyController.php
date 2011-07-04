<?php
class CompanyController extends Zend_Controller_Action
{
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

    public function confirmAction()
    {
        $companyService = ServiceLocator::getCompanyService();
        $companyService->confirmCompanyRegistration(
            $this->_getParam('id'),
            $this->_getParam('code')
        );
    }

    public function registerSuccessAction()
    {
        // just view here
    }
}
