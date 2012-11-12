<?php

define('QUICKADDNEW_MODULE', basename(dirname(__FILE__)));

if(QUICKADDNEW_MODULE != 'quickaddnew') {
	throw new Exception(
		"The addnew module must be in a directory named 'addnew', not " . QUICKADDNEW_MODULE
	);
}

Object::add_extension('OptionsetField', 'QuickAddNewExtension');
//Object::add_extension('CheckboxSetField', 'QuickAddNewExtension');