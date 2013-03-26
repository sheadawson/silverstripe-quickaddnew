<?php

define('QUICKADDNEW_MODULE', basename(dirname(__FILE__)));

if(QUICKADDNEW_MODULE != 'quickaddnew') {
	throw new Exception(
		"The addnew module must be in a directory named 'addnew', not " . QUICKADDNEW_MODULE
	);
}

DropdownField::add_extension('QuickAddNewExtension');
ListboxField::add_extension('QuickAddNewExtension');
