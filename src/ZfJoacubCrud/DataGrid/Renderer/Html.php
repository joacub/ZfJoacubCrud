<?php

namespace ZfJoacubCrud\DataGrid\Renderer;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ViewModel;
use Nette\Diagnostics\Debugger;
use Zend\Mvc\View\Http\InjectTemplateListener;

/**
 * Class Html
 * @package AtDataGrid\DataGrid\Renderer
 */
class Html extends AbstractRenderer
{
    /**
     * Template rendering engine
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $engine;

    /**
     * Html template
     *
     * @var string
     */
    protected $template = 'zf-joacub-crud/grid/list';
    
    /**
     *
     * @var string;
     */
    protected $sm = null;

    /**
     * Additional CSS rules
     *
     * @var string
     */
    protected $cssFile = '';

    /**
     * @param \Zend\View\Renderer\RendererInterface $engine
     * @return $this
     */
    public function setEngine(RendererInterface $engine)
    {
    	$this->engine = $engine;
    	return $this;
    }

    /**
     * @return \Zend\View\Renderer\RendererInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     *
     * @param string $originalTemplate
     * @return \ZfJoacubCrud\DataGrid\Renderer\Html
     */
    public function setServiceManager($sm)
    {
    	$this->sm = $sm;
    	return $this;
    }
    
    public function getServiceManager()
    {
    	return $this->sm;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setCssFile($path)
    {
        $this->cssFile = $path;
        return $this;
    }

    /**
     * @param array $options
     * @return
     */
    public function render($variables = array())
    {
        $engine = $this->getEngine();
        
        $sm = $this->getServiceManager();
        $controller = $sm->get('application');
        $controller instanceof \Zend\Mvc\Application;
        $controller->getMvcEvent()->setResult(new ViewModel());
        $injectTemplateListener  = new InjectTemplateListener();
        $injectTemplateListener->injectTemplate($controller->getMvcEvent());
        $model = $controller->getMvcEvent()->getResult();
        $originalTemplateBase = dirname($model->getTemplate());
        
        $viewResolver = $engine->resolver();
        
        //list
        $viewModel = new ViewModel($variables);
        
        $viewModel->setTemplate($originalTemplateBase . '/grid/list');
        if(false === $viewResolver->resolve($viewModel->getTemplate()))
            $viewModel->setTemplate('zf-joacub-crud/grid/list');
        
        //filters
        $viewGridflashMessenger = new ViewModel($variables);
        $viewGridflashMessenger->setTemplate($originalTemplateBase . '/grid/flash-messenger');
        if(false === $viewResolver->resolve($viewGridflashMessenger->getTemplate()))
        	$viewGridflashMessenger->setTemplate('zf-joacub-crud/grid/flash-messenger');
        
        //filters
        $viewGridFilters = new ViewModel($variables);
        $viewGridFilters->setTemplate($originalTemplateBase . '/grid/filters');
        if(false === $viewResolver->resolve($viewGridFilters->getTemplate()))
            $viewGridFilters->setTemplate('zf-joacub-crud/grid/filters');
        
        //row list
        $viewGridRowsList = new ViewModel($variables);
        $viewGridRowsList->setTemplate($originalTemplateBase . '/grid/rows/list');
        if(false === $viewResolver->resolve($viewGridRowsList->getTemplate())) {
            $viewGridRowsList->setTemplate('zf-joacub-crud/grid/rows/list');
        }
        
        //row group actions
        $viewGridRowsGoupActions = new ViewModel($variables);
        $viewGridRowsGoupActions->setTemplate($originalTemplateBase . '/grid/rows/group-actions');
        if(false === $viewResolver->resolve($viewGridRowsGoupActions->getTemplate())) {
            $viewGridRowsGoupActions->setTemplate('zf-joacub-crud/grid/group-actions');
        }
        
        $viewGridPaginator = new ViewModel($variables);
        $viewGridPaginator->setTemplate($originalTemplateBase . '/grid/paginator');
        if(false === $viewResolver->resolve($viewGridPaginator->getTemplate())) {
            $viewGridPaginator->setTemplate('zf-joacub-crud/grid/paginator');
        }
        
        // pagination control
        $viewGridPaginationControl = new ViewModel();
        $viewGridPaginationControl->setTemplate($originalTemplateBase . '/grid/pagination-control');
        if(false === $viewResolver->resolve($viewGridPaginationControl->getTemplate())) {
			$viewGridPaginationControl->setTemplate(
					'zf-joacub-crud/grid/pagination-control');
		}
		
		$viewGridPaginator->setVariable('viewGridPaginationControl', $viewGridPaginationControl->getTemplate());
		
		$viewModel->setVariable('viewGridflashMessenger', $this->getEngine()
			->render($viewGridflashMessenger))
			->setVariable('viewGridFilters', $this->getEngine()
			->render($viewGridFilters))
			->setVariable('viewGridRowsList', $this->getEngine()
			->render($viewGridRowsList))
			->setVariable('viewGridRowsGoupActions', $this->getEngine()
			->render($viewGridRowsGoupActions))
			->setVariable('viewGridPaginator', $this->getEngine()
			->render($viewGridPaginator));

        /*if (!empty($this->cssFile)) {
            $this->getView()->headLink()->appendStylesheet($this->cssFile);
        }*/

        return $engine->render($viewModel);
    }
}