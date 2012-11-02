<?php

class QuickAddNewExtension extends Extension {

	protected $addNewFields;

	protected $addNewClass;

	protected $sourceCallback;

	public static $allowed_actions = array(
		'AddNewForm',
		'AddNewFormHTML',
		'doAddNew'
	);

	public function useAddNew($class, $callback, FieldList $fields = null){
		if(!$fields){
			if(singleton($class)->hasMethod('getAddNewFields')){
				$fields = singleton($class)->getAddNewFields();
			}
		}

		if(!$fields){
			return $this->owner; //TODO throw execption?
		}
		
		$this->sourceCallback = $callback;

		$this->addNewClass = $class;
		$this->addNewFields = $fields;
		return $this->owner;
	}

	public function AddNewForm(){
		return Form::create($this->owner, 'AddNewForm', $this->addNewFields, FieldList::create(FormAction::create('doAddNew', 'Add')));
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
		return $this->owner->forTemplate();
	}

}
