<?php
// 	Copyright (c) 2010 Alan Storm
// 	
// 	Permission is hereby granted, free of charge, to any person obtaining a copy
// 	of this software and associated documentation files (the "Software"), to deal
// 	in the Software without restriction, including without limitation the rights
// 	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// 	copies of the Software, and to permit persons to whom the Software is
// 	furnished to do so, subject to the following conditions:
// 	
// 	The above copyright notice and this permission notice shall be included in
// 	all copies or substantial portions of the Software.
// 	
// 	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// 	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// 	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// 	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// 	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// 	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// 	THE SOFTWARE.


	class Alanstormdotcom_Configlint_Helper_Runner extends Mage_Core_Helper_Data
	{	
		protected $_fails	= array();
		protected $_passes	= array();		
		
		public function runLints()
		{
			//get list of lint classes
			$lint_classes = $this->getLintClasses();						
			foreach($lint_classes as $class)
			{
				$class = self::instantiateLintClass($class);
				$results = $class->run();			
				
				$this->_fails  = array_merge($this->_fails, $results['fail']);
				$this->_passes = array_merge($this->_passes, $results['pass']);				
				//$results['pass'];$results['fail'];								
			}
			
			return $this;
		}
		
		public function report($reporter=false)
		{
			$reporter = $reporter ? $reporter : Mage::helper('configlint/reporter');			
			$reporter->report($this);
		}
		
		public function getFails()
		{
			return $this->_fails;
		}

		public function getPasses()
		{
			return $this->_passes;
		}
		
		protected function getLintClasses()
		{
			$classes = array();
			$lint_config = Mage::getConfig()
			->loadModulesConfiguration('configlints.xml')        
			->getNode('lints');  
			$nodes = $lint_config->xPath('//helper_class');					
			foreach($nodes as $value)
			{
				$classes[] = (string)$value;
			}			
			return $classes;
		}
		
		static public function instantiateLintClass($string_identifier)
		{
			if(strpos($string_identifier,'/') === false)
			{
				throw new Exception('Classname ['.$string_identifier.'] must be in URI format (module/helpername)');
			}
			else
			{
				return Mage::helper($string_identifier);
			}
		}
	}
