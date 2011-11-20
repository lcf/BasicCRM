<?php
class AuthController extends Zend_Controller_Action
{
    public function loginAction()
    {
        if ($this->_request->isPost()) {
            $authService = \ServiceLocator::getAuthService();
            $session = $authService->loginUser(
                $this->_getParam('email'),
                $this->_getParam('password')
            );

            // Setting sessionid cookie header
            $expires = new Zend_Date();
            $expires->addDay(7);
            $this->getResponse()->setHeader('Set-Cookie',
                'sessionid=' . $session->getId() . '; '
                . 'expires=' . $expires->get(Zend_Date::COOKIE) . '; '
                . 'HttpOnly; '
                . 'path=/'
            );
            $this->_redirect('/company/dashboard');
        }
    }

    public function changePasswordSuccessAction()
    {
        // just view here
    }

    public function changePasswordAction()
    {
        if ($this->_request->isPost()) {
            ServiceLocator::getAuthService()->changeUserPassword(
                $this->getRequest()->getCookie('sessionid'),
                $this->getRequest()->getParam('password'),
                $this->getRequest()->getParam('new-password'),
                $this->getRequest()->getParam('confirm-new-password')
            );
            $this->_redirect('/auth/change-password-success');
        }
    }

    public function logoutAction()
    {
        $authService = \ServiceLocator::getAuthService();
        $authService->logoutUser($this->getRequest()->getCookie('sessionid'));
        // And remove the cookie
        $expires = new Zend_Date(0, Zend_Date::TIMESTAMP);
        $this->getResponse()->setHeader('Set-Cookie',
            'sessionid=; '
            . 'expires=' . $expires->get(Zend_Date::COOKIE) . '; '
            . 'HttpOnly; '
            . 'path=/'
        );
        $this->_redirect('/');
    }
}
