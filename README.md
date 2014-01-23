silverstripe-quickaddnew
============================

What is it?
--------

A decorator for form fields that manage object relationships, to allow adding a new object on the fly through a dialog window. It can handle has_one, has_many or many_many relationships. At the moment it has been tested / works on DropdownField, ListboxField and CheckboxSetField. It works both in the CMS and in the frontend. For frontend, [Select2Field or MultiSelect2Field](https://github.com/sheadawson/silverstripe-select2) are recommended.

Screenshot
--------

![Screenshot](https://raw.github.com/sheadawson/silverstripe-quickaddnew/master/images/screenshot.png)

Requirements
--------

SilverStripe 3

Usage
--------

Firstly, when creating the form field, we need to create a closure that returns the source array to populate the field's options.
We do this because later on, when the field is refreshed with the newly created Object ID as it's value, we need to use this function
Again to get up to date data for the source.

	$source = function(){
		return MyObject::get()->map()->toArray();
	};

Then we can create the form field, calling the closure as the source argument

	$field = DropdownField::create('MyObjectID', 'My Object', $source());

Next, we can tell the field to use and configure quickaddnew. The first parameter is the class name of the object that will be created. The second is the $source closure  Note: See QuickAddNewExtension::useAddNew() for the list of configurations parameters available. These allow you to customise the fields and required fields (for validation) for the dialog. By default the object class's getAddNewFields() or getCMSFields() methods are used
		
	$field->useAddNew('MyObject', $source);

Add the field to your FieldList

	$fields->addFieldToTab('Root.Main', $field);
