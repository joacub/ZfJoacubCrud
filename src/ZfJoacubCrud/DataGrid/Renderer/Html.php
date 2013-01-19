<?php

namespace ZfJoacubCrud\DataGrid\Renderer;

use Zend\View\Model\ViewModel;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Zend\Mvc\MvcEvent;
use AssetManager\Resolver\AggregateResolverAwareInterface;
use Zend\View\Resolver\AggregateResolver;
use Nette\Diagnostics\Debugger;
use Zend\View\Helper\PaginationControl;
/**
 * @todo Rename to AtAdmin\DataGrid\Renderer\ZendViewPhpRenderer
 */
class Html extends AbstractRenderer
{
    /**
     * View object
     *
     * @var \Zend\View\Renderer\PhpRenderer
     */
    protected $engine = null;
    
    /**
     * 
     * @var string;
     */
    protected $originalTemplate = null;
    
    /**
     * 
     * @var AggregateResolverAwareInterface;
     */
    protected $resolver;

    /**
     * Html template
     *
     * @var string
     */
    protected $template = 'grid/list';

    /**
     * Additional CSS rules
     *
     * @var string
     */
    protected $cssFile = '';

    /**
     * Set view object
     */
    public function setEngine(\Zend\View\Renderer\PhpRenderer $engine)
    {
    	$this->engine = $engine;
    	return $this;
    }

    /**
     * @return null|\Zend\View\Renderer\PhpRenderer
     */
    public function getEngine()
    {
        return $this->engine;
    }
    
    /**
     * 
     * @param string $originalTemplate
     * @return \ZfJoacubCrud\DataGrid\Renderer\Html
     */
    public function setOriginalTemplate($originalTemplate)
    {
        $this->originalTemplate = $originalTemplate;
        return $this;
    }
    
    public function getOriginalTemplate()
    {
        return $this->originalTemplate;
    }
    
    /**
     * 
     * @param AggregateResolverAwareInterface $resolver
     * @return \ZfJoacubCrud\DataGrid\Renderer\Html
     */
    public function setViewResolver(AggregateResolver $resolver)
    {
        $this->resolver = $resolver;
        return $this;
        
    }
    
    /**
     * 
     * @return \ZfJoacubCrud\DataGrid\Renderer\AggregateResolverAwareInterface;
     */
    public function getViewResolver()
    {
        return $this->resolver;
    }

    /**
     * @param $template
     * @return Html
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
     * @param $path
     * @return Html
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
        $viewResolver = $this->getViewResolver();
        
        //list
        $viewModel = new ViewModel($variables);
        
        $originalTemplateBase = dirname($this->getOriginalTemplate());
        
        $viewModel->setTemplate($originalTemplateBase . '/grid/list');
        if(false === $viewResolver->resolve($viewModel->getTemplate()))
            $viewModel->setTemplate('zf-joacub-crud/grid/list');
        
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
            $viewGridPaginationControl->setTemplate('zf-joacub-crud/grid/pagination-control');
        }
        
        $viewGridPaginator->setVariable('viewGridPaginationControl', $viewGridPaginationControl->getTemplate());
        
        $viewModel->setVariable('viewGridFilters', 
            $this->getEngine()
                ->render($viewGridFilters))
            ->setVariable('viewGridRowsList', 
            $this->getEngine()
                ->render($viewGridRowsList))
        ->setVariable('viewGridRowsGoupActions', 
            $this->getEngine()
                ->render($viewGridRowsGoupActions))
        ->setVariable('viewGridPaginator', 
            $this->getEngine()
                ->render($viewGridPaginator));

        /*if (!empty($this->cssFile)) {
            $this->getView()->headLink()->appendStylesheet($this->cssFile);
        }*/

        return $engine->render($viewModel);
    }
}