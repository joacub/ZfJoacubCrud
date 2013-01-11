<?php

namespace ZfJoacubCrud\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZfJoacubCrud\DataGrid;
use Zend\Debug\Debug;
use ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway;
use Nette\Diagnostics\Debugger;

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

            $viewModel = new ViewModel(array('grid' => $grid));
	        $viewModel->setTemplate('at-datagrid/grid');

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
        $form->setData($requestParams);

        if ($this->getRequest()->isPost() && $form->isValid()) {
            $formData = $this->preSave($form);
            $itemId = $grid->save($formData);
            
            if($grid->getDataSource() instanceof DoctrineDbTableGateway) {
                $grid->getDataSource()->getEm()->flush();
            }
            
            $this->postSave($grid, $itemId);

            $this->backTo()->goBack('Record created.');
        }

        $backUrl = $this->backTo()->getBackUrl(false);
        
        $viewModel = new ViewModel(array('grid' => $grid, 'backUrl' => $backUrl));
        $viewModel->setTemplate('at-datagrid/create');

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
            throw new \Exception('You are not allowed to do this.');
        }

        $itemId = $this->params('id');

        if (!$itemId) {
            throw new \Exception('No record found.');
        }

        $requestParams = $this->getRequest()->getPost();

        $form = $grid->getForm();
        $form->setData($requestParams);

        
        if ($this->getRequest()->isPost() && ($isValid = $form->isValid())) {
            $data = $this->preSave($form);
            $grid->save($data, $itemId);
            
            if($grid->getDataSource() instanceof DoctrineDbTableGateway) {
                $grid->getDataSource()->getEm()->flush();
            }
            
            $this->postSave($grid, $itemId);

            $this->backTo()->goBack('Record updated.');
        }

        $item = $grid->getRow($itemId);
        if(is_object($item)) {
            $item = $item->getArrayCopy();
        }
        
        $form->setData($item);

        //$currentPanel = $this->getRequest()->getParam('panel');
        //$this->view->panel = $currentPanel;

        $viewModel = new ViewModel(array(
            'grid' => $grid,
            'item' => $item,
            'backUrl' => $backUrl
        ));
        $viewModel->setTemplate('at-datagrid/edit');

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