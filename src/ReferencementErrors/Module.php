<?php

namespace ReferencementErrors;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use Zend\Serializer\Serializer;
use Zend\Crypt\BlockCipher;

class Module
    implements BootstrapListenerInterface,
               ConfigProviderInterface,
               ServiceProviderInterface,
               DependencyIndicatorInterface
{

	protected $recommendedErrorPhrases = array(
			// CLIENT ERROR
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			// SERVER ERROR
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			511 => 'Network Authentication Required',
	);
	
    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent|EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {

        $application    = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $eventManager = $application->getEventManager(); 
        
        //Catch all error and redirect it to the error controller
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function( MvcEvent $event ){
        	$serializer =  new \Zend\Serializer\Adapter\PhpSerialize();
        	$config = $event->getApplication()->getServiceManager()->get('config');
        	$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        	$blockCipher->setKey($config['referencement_errors']['key']);
        	
        	//Get response
        	$response = $event->getResponse();
        	
        	//Store exception in short session
        	$variables = $event->getResult()->getVariables();
        	$exception = $event->getResult()->getVariable('exception');
        	
        	$filter = new \Zend\Filter\FilterChain();
        	$filter->attach(new \Zend\Filter\StringToLower());
        	$filter->attach(new \Zend\Filter\Word\SeparatorToDash());
        	$filter->attach(new \Zend\Filter\Word\SeparatorToDash('\''));
        	
        	if(!$exception){
        		$title = $this->recommendedErrorPhrases[$response->getStatusCode()];
        		$query = $blockCipher->encrypt(Serializer::serialize(array(
        			'title'	=>	$this->recommendedErrorPhrases[$response->getStatusCode()],
        			'code'	=>	$response->getStatusCode(),
        			'reason'	=>	$variables->reason,
        			'message'	=>	$variables->message
        		)));
			}else{
				$title = $this->recommendedErrorPhrases[$exception->getCode()];
        		$query = $blockCipher->encrypt(Serializer::serialize(array(
        			'title'	=>	$this->recommendedErrorPhrases[$exception->getCode()],
        			'code'	=>	$exception->getCode(),
        			'message'	=>	$exception->getMessage()
        		)));
        	}
        	
        	
        	$url = $event->getRouter()->assemble(array('type'=>$filter->filter($title)), array('name' => 'referencement-errors'));
        	$response->getHeaders()->addHeaderLine('Location', $url.'?exception='.base64_encode($query));
        	$response->setStatusCode(302);
        	$response->sendHeaders();
        	$event->stopPropagation(true);
        	return $response;
        });
    }


    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|Traversable
     */
    public function getConfig()
    {
        $config = include __DIR__ . '/../../config/module.config.php';
        //$config['router']['routes']
        return $config;
    }

    /**
     *
     * Set autoloader config for RbacUserDoctrineOrm module
     *
     * @return array\Traversable
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        /*return array(
            'factories' => array(
                'RbacUserDoctrineOrmRoleMapper' => function ($sm) {
                    return new Mapper\Role(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('RbacUserDoctrineOrmRoleMapperOptions')
                    );
                },
                'RbacUserDoctrineOrmRoleMapperOptions' => function ($sm) {
                    $config = $sm->get('Configuration');
                    return new Options\RoleMapperOptions(
                        (isset($config['rbac-user-doctrine-orm']['mapper']['role'])
                            ? $config['rbac-user-doctrine-orm']['mapper']['role']
                            : array())
                    );
                },
                'Zend\Authentication\AuthenticationService' => function ($serviceManager) {
                	return $serviceManager->get('doctrine.authenticationservice.orm_default');
                }
            ),
        );*/
    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return array();
    }
}
