<?php
/**
 * QuickAddNewExtension
 *
 * @package silverstripe-quickaddnew
 * @author shea@silverstripe.com.au
 **/
class QuickAddNewExtension extends Extension {

	/**
	 * @var FieldList
	 **/
	protected $addNewFields;


	/**
	 * @var String
	 **/
	protected $addNewClass;


	/**
	 * @var Function
	 **/
	protected $sourceCallback;


	/**
	 * @var RequiredFields
	 **/
	protected $addNewRequiredFields;


	/**
	 * @var Boolean
	 **/
	protected $isFrontend;


	public static $allowed_actions = array(
		'AddNewForm',
		'AddNewFormHTML',
		'doAddNew'
	);


	/**
	 * Tell this form field to apply the add new UI and fucntionality
	 * @param String $class - the class name of the object being managed on the relationship
	 * @param Function $sourceCallback - the function called to repopulate the field's source array
	 * @param FieldList $fields - Fields to create the object via dialog form - defaults to the object's getAddNewFields() method
	 * @param RequiredFields $required - to create the validator for the dialog form
	 * @param Boolean $isFrontend - If this is set to true, the css classes for the CMS ui will not be set of the form elements
	 * this also opens the opportunity to manipulate the form for Frontend uses via an extension 
	 * @return FormField $this->owner
	 **/
	public function useAddNew($class, $sourceCallback, FieldList $fields = null, RequiredFields $required = null, $isFrontend = false){
		if(!is_callable($sourceCallback)){
			throw new Exception('the useAddNew method must be passed a callable $sourceCallback parameter, ' . gettype($sourceCallback) . ' passed.');
		}
		
		// if the user can't create this object type, don't modify the form
		if (!singleton($class)->canCreate()) {
			return $this->owner;
		}

		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-validate/lib/jquery.form.js');
		Requirements::javascript(QUICKADDNEW_MODULE . '/javascript/quickaddnew.js');
		Requirements::css(THIRDPARTY_DIR . '/jquery-ui-themes/smoothness/jquery-ui.css');
		Requirements::css(QUICKADDNEW_MODULE . '/css/quickaddnew.css');
		Requirements::add_i18n_javascript(QUICKADDNEW_MODULE . '/javascript/lang');
		if(!$fields){
			if(singleton($class)->hasMethod('getAddNewFields')){
				$fields = singleton($class)->getAddNewFields();
			}else{
				$fields = singleton($class)->getCMSFields();
			}
		}

		if(!$required){
			if(singleton($class)->hasMethod('getAddNewValidator')){
				$required = singleton($class)->getAddNewValidator();
			}
		}

		$this->owner->addExtraClass('quickaddnew-field');
		
		$this->sourceCallback 		= $sourceCallback;
		$this->isFrontend 			= $isFrontend;
		$this->addNewClass 			= $class;
		$this->addNewFields 		= $fields;
		$this->addNewRequiredFields = $required;

		return $this->owner;
	}


	/**
	 * The AddNewForm for the dialog window
	 *
	 * @return Form
	 **/
	public function AddNewForm(){
		$action 	= FormAction::create('doAddNew', _t('QUICKADDNEW.Add', 'Add'))->setUseButtonTag('true');

		if(!$this->isFrontend){
			$action->addExtraClass('ss-ui-action-constructive')->setAttribute('data-icon', 'accept');
		}

		$actions 	= FieldList::create($action);
		$form 		= Form::create($this->owner, 'AddNewForm', $this->addNewFields, $actions, $this->addNewRequiredFields);

		$this->owner->extend('updateQuickAddNewForm', $form);

		return $form;

	}


	/**
	 * Returns the HTML of the AddNewForm for the dialog
	 *
	 * @return String
	 **/
	public function AddNewFormHTML(){
		return $this->AddNewForm()->forTemplate();
	}


	/**
	 * Handles adding the new object
	 * Returns the updated FieldHolder of this form to replace the existing one
	 * @return String
	 **/
	public function doAddNew($data, $form){
		$obj = Object::create($this->addNewClass);
		if (!$obj->canCreate()) {
			return Security::permissionFailure(Controller::curr(), "You don't have permission to create this object");
		}
		$form->saveInto($obj);
		
		try {
			$obj->write();	
		} catch (Exception $e) {
			$form->setMessage($e->getMessage(), 'error');
			return $form->forTemplate();
		}

		$callback = $this->sourceCallback;
		$items = $callback($obj);
		$this->owner->setSource($items);

		// if this field is a multiselect field, we add the new Object ID to the existing
		// options that are selected on the field then set that as the value
		// otherwise we just set the new Object ID as the value
		if(isset($data['existing'])){
			$existing = $data['existing'];
			$value = explode(',', $existing);
			$value[] = $obj->ID;
		}else{
			$value = $obj->ID;
		}

		$this->owner->setValue($value);
		$this->owner->setForm($form);
		return $this->owner->FieldHolder();

	}


	public function getIsFrontend(){
		return $this->isFrontend;
	}

}
