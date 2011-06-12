<?php
class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $message = 'Page not found';
                break;
            default:
                if ($errors->exception instanceof DomainException) {
                    $message = $errors->exception->getMessage();
                    if (!$message) {
                        $message = 'Unknown error';
                    }
                } else {
                    $this->getResponse()->setHttpResponseCode(500);
                    $message = 'Application error';
                }
                break;
        }
        $this->view->assign('message', $message);
    }
}

