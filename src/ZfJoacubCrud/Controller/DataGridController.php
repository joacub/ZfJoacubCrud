<?php

namespace ZfJoacubCrud\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfJoacubCrud\DataGrid;
use Zend\Debug\Debug;
use ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway;
use Nette\Diagnostics\Debugger;
use Zend\Navigation\Navigation;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Zend\Loader\AutoloaderFactory;
use CustomediaGestionSuppliers\Entity\Suppliers;
use DoctrineModule\Stdlib\Hydrator\Strategy\DisallowRemoveByValue;
use Zend\Form\FormInterface;

class DataGridController extends AbstractActionController
{
    /**
     * @var \ZfJoacubCrud\DataGrid\DataGrid
     */
    protected $grid;
    
    /**
     * @return array|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * @return void
     */
    public function listAction()
    {
        $this->backTo()->setBackUrl();

        // Get grid object
    	$grid = $this->getGrid();

        if (!isset($_POST['cmd'])) {
            $requestParams = $this->getRequest()->getQuery();

            $filtersForm = $grid->getFiltersForm();
            $filtersForm->setData($requestParams);

            if ($filtersForm->isValid()) {
                $grid->applyFilters($filtersForm->getData());
            }

            $varsModel = array('grid' => $grid);
            
            $viewModel = new ViewModel($varsModel);
            
            $this->getEvent()->setResult($viewModel);
            $injectTemplateListener  = new InjectTemplateListener();
            $injectTemplateListener->injectTemplate($this->getEvent());
            $model = $this->getEvent()->getResult();
            $originalTemplate = $model->getTemplate();
            $originalTemplateBase = dirname($originalTemplate);
            
            $viewResolver = $this->getServiceLocator()->get('ViewResolver');
            
            //miramos si existe el original
            if(false === $viewResolver->resolve($originalTemplate))
                $viewModel->setTemplate('zf-joacub-crud/grid');
            
            return $viewModel;
        } else {
            return $this->forward()->dispatch($this->params('controller'), array('action' => $this->params()->fromPost('cmd')));
        }
    }

    // CRUD

    /**
     * @throws \Exception
     */
    public function createAction()
    {
        $grid = $this->getGrid();
        
        if (!$grid->isAllowCreate()) {
            throw new \Exception('You are not allowed to do this.');
        }

        $requestParams = $this->getRequest()->getPost();

        $form = $grid->getForm();
        
        $entityClassName = $grid->getDataSource()->getEntity();
        $entity = new $entityClassName();
        
        if($this->grid->getDataSource()->isTranslationTable()) {
            $entity->setLocale($this->params()->fromQuery('locale', \Locale::getDefault()));
            
            //hacemos esto por si no esta definida la columna locale dentro de la entidad y es una referencia inexistente utilizada internamente
            $form->get('locale')->setValue($entity->getLocale());
        }
        
        $form->bind($entity);
                
        if ($this->getRequest()->isPost()) { 
            
            $form->setData($requestParams);
            
            if($form->isValid()) {
                
                $formData = $this->preSave($form);
                
                $itemId = $grid->save($formData);
                
                if($grid->getDataSource() instanceof DoctrineDbTableGateway) {
                    $grid->getDataSource()->getEm()->flush();
                }
                
                $this->postSave($grid, $itemId);
                
                $this->backTo()->goBack('Record created.');
                
            } else {
            	//algo no es valido
            	Debugger::dump($form);
            	
            	foreach ($form->getFieldsets() as $fieldset) {
            		Debugger::dump($fieldset->getMessages());
            	}
            	
            }
        }
            

        $backUrl = $this->backTo()->getBackUrl(false);
        
        $viewModel = new ViewModel(array('grid' => $grid, 'backUrl' => $backUrl));
        
        $this->getEvent()->setResult($viewModel);
        $injectTemplateListener  = new InjectTemplateListener();
        $injectTemplateListener->injectTemplate($this->getEvent());
        $model = $this->getEvent()->getResult();
        $originalTemplate = $model->getTemplate();
        $originalTemplateBase = dirname($originalTemplate);
        
        $viewResolver = $this->getServiceLocator()->get('ViewResolver');
        
        //miramos si existe el original
        if(false === $viewResolver->resolve($originalTemplate))
            $viewModel->setTemplate('zf-joacub-crud/create');
        
        return $viewModel;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function editAction()
    {
        
        $backUrl = $this->backTo()->getBackUrl(false);

        $grid = $this->getGrid();
        
        if (!$grid->isAllowEdit()) {
            throw new \Exception('No se le permite hacer esto.');
        }

        $itemId = $this->params('id');

        if (!$itemId) {
            throw new \Exception('No se encontró registro.');
        }
        
        $entity = $grid->getRow($itemId);
        
        $requestParams = $this->getRequest()->getPost();

        $form = $grid->getForm();
        
        
        $entityClassName = $grid->getDataSource()->getEntity();
       // Debugger::dump($requestParams->toArray());
        
        
        /*$entity = $form->getHydrator()->hydrate($requestParams->toArray(), $entity);
        Debugger::dump($entity->getCategories());exit('ad');
        $identifier = $grid->getDataSource()->getIdentifierFieldName();
        
        $entity =  new $entityClassName();
        $entity->{"set$identifier"}($itemId);*/
        $form->bind($entity);
        $form->setData($requestParams);
        if ($this->getRequest()->isPost() && ($isValid = $form->isValid())) {
            
            $data = $this->preSave($form);
            
            $grid->save($data, $itemId);
            
            if($grid->getDataSource() instanceof DoctrineDbTableGateway) {
                $grid->getDataSource()->getEm()->flush();
            }
            
            $this->postSave($grid, $itemId);

            $this->backTo()->goBack('Registro actualizado.');
        }

        $item = $grid->getRow($itemId);
        
        if (!$item) {
            throw new \Exception('No se encontró registro.');
        }
        
        if(method_exists($item, 'setLocale')) {
            $item->setLocale($this->params()->fromQuery('locale', \Locale::getDefault()));
            $this->getGrid()->getDataSource()->getEm()->refresh($item);
            $form->bind($item);
            $form->get('locale')->setValue($item->getLocale());
            
        } else {
            
            $form->bind($item);
        }
        if(is_object($item)) {
            $item = $item->getArrayCopy();
        }
        
        if(!$grid->getCaption())
            $grid->setCaption($item[$grid->getTitleColumnName()]);
        
        
        $serviceLocator = $this->getServiceLocator()->get('Application');
        $routeMatch  = $serviceLocator->getMvcEvent()->getRouteMatch();
        $router      = $serviceLocator->getMvcEvent()->getRouter();
        $routeMatchName = $routeMatch->getMatchedRouteName();
        
        $navigation = $this->getEvent()->getApplication()->getServiceManager()->get('viewrenderer')->getEngine()->plugin('navigation', array('navigation'));
        
        $container = $navigation->setContainer('admin_navigation')->getContainer();
        $container instanceof \Zend\Navigation\Navigation;
        
        $container = $container->findOneBy('route',
            $routeMatchName);
        
        if($container) {
            $container = $container->findOneBy('params', array('action' => 'list'));
        } 
        
        if($container) {
            $container instanceof \Zend\Navigation\Page\Mvc;
            $pages = new \Zend\Navigation\Page\Mvc(
                array(
                    'label' => $grid->getCaption(),
                    'route' => $routeMatchName,
                    'params' => array(
                        'action' => 'edit',
                        'id' => $this->params('id')
                    ),
                    'visible' => false
                ));
            
            
            $pages->setRouteMatch($routeMatch);
            $pages->setDefaultRouter($router);
            
            $container->addPage($pages);
        }

        //$currentPanel = $this->getRequest()->getParam('panel');
        //$this->view->panel = $currentPanel;
        
        $varsModel = array(
            'grid' => $grid,
            'item' => $item,
            'backUrl' => $backUrl
        );

        $viewModel = new ViewModel($varsModel);
        
        $this->getEvent()->setResult($viewModel);
        $injectTemplateListener  = new InjectTemplateListener();
        $injectTemplateListener->injectTemplate($this->getEvent());
        $model = $this->getEvent()->getResult();
        $originalTemplate = $model->getTemplate();
        $originalTemplateBase = dirname($originalTemplate);
        
        $viewResolver = $this->getServiceLocator()->get('ViewResolver');
        
        //miramos si existe el original
        if(false === $viewResolver->resolve($originalTemplate))
            $viewModel->setTemplate('zf-joacub-crud/edit');
        
        //sumary panels
        $viewPanelSumary = new ViewModel($varsModel);
        $viewPanelSumary->setTemplate($originalTemplateBase . '/panels/summary');
        if(false === $viewResolver->resolve($viewPanelSumary->getTemplate()))
            $viewPanelSumary->setTemplate('zf-joacub-crud/panels/summary');
        
        //formulario
        $viewForm = new ViewModel($varsModel);
        $viewForm->setTemplate($originalTemplateBase . '/form');
        if(false === $viewResolver->resolve($viewForm->getTemplate()))
            $viewForm->setTemplate('zf-joacub-crud/form');
        
        $viewModel->addChild($viewPanelSumary, 'viewPanelsSumary')
        ->addChild($viewForm, 'viewForm');
        
        
        /**
         * esto no es necesario pero podria serlo
        $viewResolver = $this->getServiceLocator()->get('ViewResolver');
        
        $module = substr(get_class($this), 0, strpos(get_class($this), '\\'));
        
        $config = $this->getServiceLocator()->get('Configuration');
        $viewResolver = $this->getServiceLocator()->get('ViewResolver');
        $themeResolver = new \Zend\View\Resolver\AggregateResolver();
        if (isset($config[$module]['ZfJoacubCrud']['template_map'])){
            $viewResolverMap = $this->getServiceLocator()->get('ViewTemplateMapResolver');
            $viewResolverMap->add($config[$module]['ZfJoacubCrud']['template_map']);
            $mapResolver = new \Zend\View\Resolver\TemplateMapResolver(
                $config[$module]['ZfJoacubCrud']['template_map']
            );
            $themeResolver->attach($mapResolver);
        }
        
        if (isset($config[$module]['ZfJoacubCrud']['template_path_stack'])){
            $viewResolverPathStack = $this->getServiceLocator()->get('ViewTemplatePathStack');
            $viewResolverPathStack->addPaths($config[$module]['ZfJoacubCrud']['template_path_stack']);
            $pathResolver = new \Zend\View\Resolver\TemplatePathStack(
                array('script_paths'=>$config[$module]['ZfJoacubCrud']['template_path_stack'])
            );
            $defaultPathStack = $this->getServiceLocator()->get('ViewTemplatePathStack');
            $pathResolver->setDefaultSuffix($defaultPathStack->getDefaultSuffix());
            $themeResolver->attach($pathResolver);
        }
        
        $viewResolver->attach($themeResolver, 100);
        */
        
        return $viewModel;
    }

    /**
     * @throws \Exception
     */
    public function deleteAction()
    {
        $grid = $this->getGrid();

        if (!$grid->isAllowDelete()) {
            throw new \Exception('You are not allowed to do this.');
        }

        $itemId = $this->params()->fromPost('items', $this->params('id'));

        if (!$itemId) {
            throw new \Exception('No record found.');
        }

        foreach((array)$itemId as $id) {
            $grid->delete($id);
        }
        
        if($grid->getDataSource() instanceof DoctrineDbTableGateway) {
            $grid->getDataSource()->getEm()->flush();
        }
        

        return $this->backTo()->goBack('Record deleted.');
         
    }

    /**
     * Hook before save row
     * @todo: Use event here. See ZfcBase EventAwareForm
     *
     * @param $form
     * @return mixed
     */
    public function preSave($form)
    {
        $data = $form->getData();
        return $data;
    }

    /**
     * Hook after save row
     * @todo Use event here
     *
     * @param $grid
     * @param $primary
     */
    public function postSave($grid, $primary)
    {
        return;
    }

    /**
     * @return \ZfJoacubCrud\DataGrid\DataGrid
     */
    public function getGrid()
    {
        return $this->grid;
    }
}