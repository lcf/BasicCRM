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

    public function usersAction()
    {
        $this->view->assign('users', ServiceLocator::getCompanyService()->listCompanyUsers(
            $this->getRequest()->getCookie('sessionid')));
    }

    public function dashboardAction()
    {
        // just view here for now
    }

    public function registerSuccessAction()
    {
        // just view here
    }

    public function indexAction()
    {
        // just view here
    }

    public function addUserAction()
    {
        if ($this->_request->isPost()) {
            ServiceLocator::getCompanyService()->addUserToCompany(
                $this->getRequest()->getCookie('sessionid'),
                $this->getRequest()->getParam('name'),
                $this->getRequest()->getParam('email')
            );
            $this->_redirect('/company/users');
        }
    }

    public function switchAdminAction()
    {
        if ($this->_request->isPost()) {
            ServiceLocator::getCompanyService()->switchAdmin(
                $this->getRequest()->getCookie('sessionid'),
                $this->getRequest()->getParam('password'),
                $this->getRequest()->getParam(('newadminid'))
            );
            $this->_redirect('/company/users');
        } else {
            $this->view->assign('users', ServiceLocator::getCompanyService()->listCompanyUsers(
                $this->getRequest()->getCookie('sessionid')));
        }
    }
}
