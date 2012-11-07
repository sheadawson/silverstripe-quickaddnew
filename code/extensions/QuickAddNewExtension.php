<?php

class QuickAddNewExtension extends Extension {

	protected $addNewFields;

	protected $addNewClass;

	protected $sourceCallback;

	protected $addNewRequiredFields;

	public static $allowed_actions = array(
		'AddNewForm',
		'AddNewFormHTML',
		'doAddNew'
	);

	public function useAddNew($class, $callback, FieldList $fields = null, RequiredField $required = null){
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-validate/lib/jquery.form.js');
		Requirements::javascript(QUICKADDNEW_MODULE . '/javascript/quickaddnew.js');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::css(QUICKADDNEW_MODULE . '/css/quickaddnew.css');

		if(!$fields){
			if(singleton($class)->hasMethod('getAddNewFields')){
				$fields = singleton($class)->getAddNewFields();
			}
		}

		if(!$required){
			if(singleton($class)->hasMethod('getAddNewValidator')){
				$required = singleton($class)->getAddNewValidator();
			}
		}

		if(!$fields){
			return $this->owner; //TODO throw execption?
		}

		$this->owner->addExtraClass('quickaddnew-field');
		
		$this->sourceCallback = $callback;

		$this->addNewClass 			= $class;
		$this->addNewFields 		= $fields;
		$this->addNewRequiredFields = $required;

		return $this->owner;
	}

	public function AddNewForm(){
		$actions = FieldList::create(FormAction::create('doAddNew', 'Add')->setUseButtonTag('true'));
		return Form::create($this->owner, 'AddNewForm', $this->addNewFields, $actions, $this->addNewRequiredFields);
	}

	public function AddNewFormHTML(){
		return $this->AddNewForm()->forTemplate();
	}

	public function doAddNew($data, $form){
		$obj = Object::create($this->addNewClass);
		$form->saveInto($obj);
		$obj->write();

		$callback = $this->sourceCallback;
		$items = $callback();
		$this->owner->setSource($items);
		$this->owner->setValue($obj->ID);
		$this->owner->setForm($form);
		return $this->owner->FieldHolder();
	}

}
