<?php
//		 Copyright (c) 2010 Alan Storm
//		 
//		 Permission is hereby granted, free of charge, to any person obtaining a copy
//		 of this software and associated documentation files (the "Software"), to deal
//		 in the Software without restriction, including without limitation the rights
//		 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
//		 copies of the Software, and to permit persons to whom the Software is
//		 furnished to do so, subject to the following conditions:
//		 
//		 The above copyright notice and this permission notice shall be included in
//		 all copies or substantial portions of the Software.
//		 
//		 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//		 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//		 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//		 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//		 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
//		 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
//		 THE SOFTWARE.


/*
 * @category		Alanstormdotcom
 * @package			Alanstormdotcom_Contributedlints
 * @author			Alan Storm
 * @author			Kristof Ringleff
 * @copyright		Copyright (c) 2010 Alan Storm
 * @copyright		Copyright (c) 2010 Fooman Ltd (http://www.fooman.co.nz)
 * @license			see above
 */

class Alanstormdotcom_Contributedlints_Helper_Lints_Classfiles extends Alanstormdotcom_Configlint_Helper_Lints_Abstract
{			 
	const PRINT_FILES = false;
	
	protected function setWhichConfig()
	{
		return self::FLAG_ETC_CURRENT_CONFIG;	 
	}
	
	
	/**
	* Tests that classes are the same content when loaded via Magento's autoloader
	*			 
	*/			 
	public function lintClassfileVsAutoloader($config)
	{
		 $errors = array();
		 $warnings = array();
		 $classTypes = array ('Model', 'Block', 'Helper');
		 $autoloader = new Alanstormdotcom_Contributedlints_Helper_Autoload;
		 
		 if (self::PRINT_FILES) {
				 echo "<pre>";
		 }
		 $modules = $config->modules;
		 foreach ( $modules->children() as $moduleName => $module) {

			 $extDir = Mage::getConfig()->getModuleDir('', $moduleName);
			 if (self::PRINT_FILES) {
					 echo $moduleName.": \n\t".$extDir."\n";
			 }
			 if (!is_dir($extDir)) {
					 $errors[] = 'Missing directory '.$extDir.' for module '.$moduleName; 
					 continue;	 
			 }
			 foreach ($classTypes as $classType ) {
				 $typeDir = Mage::getConfig()->getModuleDir('', $moduleName).DS.$classType;
				 try {
					 foreach( new Alanstormdotcom_Contributedlints_Helper_ExtensionFilter(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($typeDir)),'php') as $item ) {
						 if (self::PRINT_FILES) {
								 echo "\t\t".str_replace($extDir,'',$item)."\n";
						 };
						 $fileContent = file_get_contents($item);
						 preg_match_all('/\s*class\s+([a-zA-Z0-9_]+)/', $this->removeFileComments($fileContent), $matches);
						 if (sizeof($matches[1]) == 0) {
								 $warnings[] = 'no class found in file '.$item; //could be interface
								 continue;
						 }
						 if (sizeof($matches[1]) >1 ) {
								 //if "class" appears in code not commented out it will also be matched
								 //ignore known occurence in tax extension
								 if ( trim($matches[1][0]) != 'Mage_Tax_Model_Mysql4_Rule' && trim($matches[1][0]) != 'Alanstormdotcom_Contributedlints_Helper_Lints_Classfiles') {
										 $errors[] = $item . ' contains multiple classes'; 
								 }
						 }
						 
						 if($this->isKnownCoreFile(trim($matches[1][0])))
						 {
						 	continue;
						 }
						 
						 //skip known classes with bugs/errors
						 //moved to isKnownCoreFile
// 						 if (strpos(trim($matches[1][0]),'Mage_Admin_Model_Acl_Assert_') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Api_Model_Acl_Assert_') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Adminhtml_Model_Extension') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Dataflow_Model_Session_Adapter_Http') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Dataflow_Model_Session_Adapter_Http') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Gallery') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_GoogleCheckout_Model_Api_Xml_Calculate') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Adminhtml_Block_Widget_Grid_Block') ===0) {
// 								 continue;
// 						 }
// 						 if (strpos(trim($matches[1][0]),'Mage_Dataflow_Model_Convert_Iterator_') ===0) {
// 								 continue;
// 						 }

						 $classFile = $autoloader->getClassFile(trim($matches[1][0]));

						 if (trim($fileContent) != trim($classFile)) {
								 $errors[] = 'Class content from file '. $item . ' is different to class loaded via Magento. Possible explanations: <br/>1.) Class is overridden in /app/code/local/Mage or /app/code/community/Mage <br/>2.) Class name '.$matches[1][0].' should be in a different file/folder.'; 
						 }													 
					 }
				 } catch (Exception $e) {
					 $warnings[] = 'Missing directory '.$typeDir.' for module '.$moduleName;
					 //TODO: check against config.xml if blocks, helper or models are defined
				 }
			 }
	 	}
		 if(count($errors) > 0)
		 {
				 $this->fail("<p>".implode("</p><p>", $errors)."</p>");
		 }
	}
	
	protected function fetchKnownBadCoreClasses()
	{
		$original = array(
		'Mage_Admin_Model_Acl_Assert_',
		'Mage_Api_Model_Acl_Assert_',
		'Mage_Adminhtml_Model_Extension',
		'Mage_Dataflow_Model_Session_Adapter_Http',
		'Mage_Dataflow_Model_Session_Adapter_Http',
		'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Attribute_Backend_Gallery',
		'Mage_GoogleCheckout_Model_Api_Xml_Calculate',
		'Mage_Adminhtml_Block_Widget_Grid_Block',
		'Mage_Dataflow_Model_Convert_Iterator_',);
		
		$in_magento_one_four = array(
		'Mage_Core_Model_Mysql4_Design_Theme',
		'Mage_Adminhtml_Block_Tree',
		'Mage_Adminhtml_Model_System_Config_Source_Package',
		'Mage_Catalog_Model_Mysql4_Convert',
		'Mage_Dataflow_Model_Session_Adapter_Iterator',
		'Mage_Paygate_Model_Authorizenet_Request',
		'Mage_Sales_Model_Shipping_Rule_Abstract',
		'Mage_Sales_Model_Shipping_Rule_Action_Abstract',
		'Mage_Sales_Model_Shipping_Rule_Action_Carrier',
		'Mage_Sales_Model_Shipping_Rule_Action_Method',
		'Mage_Sales_Model_Shipping_Rule_Condition_Abstract',
		'Mage_Sales_Model_Shipping_Rule_Condition_Dest_Country',
		'Mage_Sales_Model_Shipping_Rule_Condition_Dest_Region',
		'Mage_Sales_Model_Shipping_Rule_Condition_Dest_Zip',
		'Mage_Sales_Model_Shipping_Rule_Condition_Order_Subtotal',
		'Mage_Sales_Model_Shipping_Rule_Condition_Order_Totalqty',
		'Mage_Sales_Model_Shipping_Rule_Condition_Package_Weight',
		'Mage_Usa_Tax_Uszipcode',		
		);
		
		return array_merge($original, $in_magento_one_four);
	}
	
	protected function isKnownCoreFile($class)
	{
		$known_bad = $this->fetchKnownBadCoreClasses();
		foreach($known_bad as $bad)
		{
			 if (strpos($class,$bad) === 0) {
			 	return true;
			 }
		
		}
		//var_dump($class);
		return false;
	}
	
	public function removeFileComments($fileStr)
	{	 
		$withoutComments = '';
		foreach (token_get_all($fileStr) as $token ) {	
			 $commentTokens = array(T_COMMENT);	
			 if (defined('T_DOC_COMMENT'))
			 $commentTokens[] = T_DOC_COMMENT;
			 if (defined('T_ML_COMMENT'))
			 $commentTokens[] = T_ML_COMMENT;
			 if (is_array($token)) {
				 if (in_array($token[0], $commentTokens)) {
						 continue;
				 }	
			 	$token = $token[1];
			 }	
			$withoutComments	.= $token;
		}
		return $withoutComments;
	}


}

