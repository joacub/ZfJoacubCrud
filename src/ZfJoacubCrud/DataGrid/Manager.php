<?php

namespace ZfJoacubCrud\DataGrid;

use ZfJoacubCrud\DataGrid\Renderer\AbstractRenderer;
use Zend\Form\Form;
use Zend\Form\FormInterface;
use Zend\ServiceManager\ServiceManager;
use Nette\Diagnostics\Debugger;

/**
 * Class Manager
 * @package AtDataGrid\DataGrid
 */
class Manager
{
    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var AbstractRenderer
     */
    protected $renderer;

    /**
     * @var bool
     */
    protected $allowCreate = true;

    /**
     * @var bool
     */
    protected $allowDelete = true;

    /**
     * @var bool
     */
    protected $allowEdit = true;

    /**
     * Actions for row
     *
     * @var array
     */
    protected $actions = array(
        'edit'   => array('action' => 'edit', 'label' => 'View & Edit', 'bulk' => false, 'button' => true, 'class' => 'icon-eye-open'),
        'delete' => array('action' => 'delete', 'label' => 'Delete', 'confirm-message' => 'Are you sure?', 'bulk' => true, 'button' => false)
    );
    
    protected $sl;

    /**
     * @param $grid
     */
    public function __construct(DataGrid $grid, ServiceManager $sm)
    {
        $this->grid = $grid;
        $this->sl = $sm;
    }

    /**
     * @return DataGrid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param bool $flag
     * @return DataGrid
     */
    public function setAllowCreate($flag = true)
    {
        $this->allowCreate = $flag;
        return $this;
    }

    /**
     * Alias for setAllowCreate
     *
     * @param bool $flag
     * @return DataGrid
     */
    public function allowCreate($flag = true)
    {
        $this->setAllowCreate($flag);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowCreate()
    {
        return $this->allowCreate;
    }

    /**
     * @param bool $flag
     * @return DataGrid
     */
    public function setAllowDelete($flag = true)
    {
        $this->allowDelete = $flag;
        return $this;
    }

    /**
     * Alias for setAllowDelete
     *
     * @param bool $flag
     * @return DataGrid
     */
    public function allowDelete($flag = true)
    {
        $this->setAllowDelete($flag);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowDelete()
    {
        return $this->allowDelete;
    }

    /**
     * @param bool $flag
     * @return DataGrid
     */
    public function setAllowEdit($flag = true)
    {
        $this->allowEdit = $flag;
        return $this;
    }

    /**
     * Alias for setAllowEdit
     *
     * @param bool $flag
     * @return DataGrid
     */
    public function allowEdit($flag = true)
    {
        $this->setAllowEdit($flag);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowEdit()
    {
        return $this->allowEdit;
    }

    /**
     * Generate form for create/edit row
     *
     * @param array $options
     * @return mixed|\Zend\Form\Form
     */
    public final function getForm($options = array())
    {
    	if ($this->form == null) {
    		if(method_exists($this, 'getCustomForm') && $this->getCustomForm($options) != false) {
    			$form = $this->getCustomForm($options);
    		} else {
    			//obtenemos el formulario automaticamente de la entidad en caso de no tener un formulario personalizado
    			$form = $this->getServiceManager()->get('formGenerator')
    			->setClass($this->getGrid()->getDataSource()->getEntity())
    			->getForm();
    		}
    
    		//prepara el formulario
    		// quita elementos que tenga que quitar y mas
    		$this->prepareForm($form);
    
    	}
    
    	return $this->form;
    }
    
    protected function getCustomForm($options = array())
    {
    	return null;
    }
    
    public function prepareForm(FormInterface $form)
    {
    	// Collect elements
    	foreach ($this->getGrid()->getColumns() as $column) {
    		 
    		 
    		$formElement = $form->get($column->getName());
    
    		if(!$formElement) {
    			$fieldsets = $form->getFieldsets();
    			 
    			foreach($fieldsets as $fieldset) {
    				$formElement = $fieldset->get($column->getName());
    				if($formElement)
    					break;
    			}
    		}
    
    		if($formElement) {
    			$optionsElement = $column->getFormElement()->getOptions();
    			$column->setFormElement($formElement);
    			$formElement->setOptions($optionsElement + $formElement->getOptions());
    		}
    		 
    		if (!$column->isVisibleInForm()) {
    			 
    			$form->remove($column->getName());
    			$form->getInputFilter()->remove($column->getName());
    			 
    			if(isset($fieldset)) {
    				$form->getInputFilter()->get($fieldset->getName())->remove(($column->getName()));
    				$fieldset->remove($column->getName());
    				$fieldset = false;
    			}
    			 
    			continue;
    		}
    		continue;
    		/* @var \Zend\Form\Element */
    		$element = $column->getFormElement();
    		 
    		if(!$form->get($column->getName())->getLabel()) {
    			$form->get($column->getName())->setLabel($column->getLabel());
    		}
    		 
    	}
    	 
    	if(!$this->getserviceManager()->get('response')->getHeaders()->has('ZfJoacubFormJqueryValidate')) {
    		//Hash element to prevent CSRF attack
    		$csrf = new \Zend\Form\Element\Csrf('hash');
    		$form->add($csrf);
    	}
    	 
    	// Use this method to add additional element to form
    	// @todo Use Event instead
    	$form = $this->addExtraFormElements($form);
    	 
    	$this->form = $form;
    	
    	return $form;
    }

    /**
     * @todo use events instead
     * @param $form
     * @return mixed
     */
    public function addExtraFormElements($form)
    {
        return $form;
    }

    /**
     * @param Renderer\AbstractRenderer $renderer
     * @return $this
     */
    public function setRenderer(AbstractRenderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * @return Renderer\AbstractRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Render grid with current renderer
     *
     * @return mixed
     */
    public function render()
    {
        $grid = $this->getGrid();

        $data                = array();
        $data['gridManager'] = $this;
        $data['grid']        = $this->getGrid();  // todo: remove it
        $data['columns']     = $grid->getColumns();
        $data['data']        = $grid->getData();
        $data['paginator']   = $grid->getDataSource()->getPaginator();

        return $this->getRenderer()->render($data);
    }

    /**
     * @param $name
     * @param array $action
     * @return DataGrid
     * @throws \Exception
     */
    public function addAction($name, $action = array())
    {
        if (!is_array($action)) {
            throw new \Exception('Row action must be an array with `action`, `label` and `confirm-message` keys');
        }

        if (!array_key_exists('action', $action)) {
            throw new \Exception('Row action must be an array with `action`, `label` and `confirm-message` keys');
        }

        if (!array_key_exists('label', $action)) {
            throw new \Exception('Row action must be an array with `action`, `label` and `confirm-message` keys');
        }

        if (!array_key_exists('bulk', $action)) {
            $action['bulk'] = true;
        }

        if (!array_key_exists('button', $action)) {
            $action['button'] = false;
        }

        $this->actions[$name] = $action;
        return $this;
    }

    /**
     * @param array $actions
     * @return DataGrid
     */
    public function addActions($actions = array())
    {
        foreach ($actions as $name => $action) {
            $this->addAction($name, $action);
        }

        return $this;
    }

    /**
     * @param $name
     * @return DataGrid
     */
    public function removeAction($name)
    {
        if (array_key_exists($name, $this->actions)) {
            unset($this->actions[$name]);
        }

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function getAction($name)
    {
        if (array_key_exists($name, $this->actions)) {
            return $this->actions[$name];
        }

        return false;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return array
     */
    public function getButtonActions()
    {
        $actions = array();

        foreach ($this->actions as $action) {
            if ($action['button'] == true) {
                $actions[] = $action;
            }
        }

        return $actions;
    }
    
    public function setServiceManager($serviceLocator)
    {
    	$this->sl = $serviceLocator;
    	return $this;
    }
    
    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
    	return $this->sl;
    }
}