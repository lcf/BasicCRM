<?php
class SessionActionHelper extends \Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $authService = \ServiceLocator::getAuthService();
        $sessionId = $this->getRequest()->getCookie('sessionid');
        if ($sessionId) {
            try {
                $session = $authService->viewSession($sessionId);
                // Switching layout
                $this->_actionController->view->layout()->setLayout('company');
                // Save session data for possible further use in the View:
                $this->_actionController->view->assign('session', $session);
            } catch (DomainException $error) {
                // An error catched during session retrieval, we remove the cookie
                $expires = new Zend_Date(0, Zend_Date::TIMESTAMP);
                $this->getResponse()->setHeader('Set-Cookie',
                    'sessionid=; '
                    . 'expires=' . $expires->get(Zend_Date::COOKIE) . '; '
                    . 'HttpOnly; '
                    . 'path=/'
                );
            }
        }
    }
}
