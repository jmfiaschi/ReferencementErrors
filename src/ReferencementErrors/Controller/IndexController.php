<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ReferencementErrors\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfcRbac\Service\AuthorizationService;
use Zend\Crypt\BlockCipher;
use Zend\Serializer\Serializer;

class IndexController extends AbstractActionController
{
	protected $authorizationService;
	
    public function indexAction()
    {
    	$viewModel = new ViewModel();
    	$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
    	$config = $this->getServiceLocator()->get('config');
    	$blockCipher->setKey($config['referencement_errors']['key']);
    	
    	$exception = $this->Params()->fromQuery('exception');
    	if($exception){
    		$exception = base64_decode($exception);
	    	$exception = $blockCipher->decrypt($exception);
	    	$exception = Serializer::unserialize($exception);
	    	$viewModel->setVariables($exception);
    	}
    	$this->getResponse()->setStatusCode($exception['code']);
    	$viewModel->setTemplate('error/index');
        return $viewModel;
    }
}
