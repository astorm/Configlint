<?php
//  Copyright (c) 2010 Alan Storm
//  
//  Permission is hereby granted, free of charge, to any person obtaining a copy
//  of this software and associated documentation files (the "Software"), to deal
//  in the Software without restriction, including without limitation the rights
//  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
//  copies of the Software, and to permit persons to whom the Software is
//  furnished to do so, subject to the following conditions:
//  
//  The above copyright notice and this permission notice shall be included in
//  all copies or substantial portions of the Software.
//  
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
//  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
//  THE SOFTWARE.

abstract class Alanstormdotcom_Configlint_Helper_Lints_Abstract
{
    static protected $_configs  =array();               
    
    protected $_whichConfig     =false;
    protected $_lastPassFail    =null;
    protected $_allPass         =array();
    protected $_allFail         =array();
    
    public function run()
    {
        $this->_whichConfig = $this->setWhichConfig();
        $config = $this->loadConfig();          
        foreach(get_class_methods($this) as $method)
        {
            if(strpos($method, 'lint') ===0)
            {
                $this->_lastPassFail=true;
                call_user_func_array(array($this, $method), array($config));
                if($this->_lastPassFail === true)
                {
                    $this->_allPass[] = 'PASSED: ' . get_class($this) . '::' . $method;
                }
            }
        }
        
        return array(
        'pass'=>$this->_allPass,
        'fail'=>$this->_allFail,
        );
    }           

    private function loadConfig()
    {
        if(array_key_exists($this->_whichConfig,self::$_configs))
        {
            return self::$_configs[$this->_whichConfig];
        }           
        
        if($this->_whichConfig == self::FLAG_ETC_BASE_CONFIG)
        {
            self::$_configs[$this->_whichConfig] = Mage::getModel('core/config')->loadBase()->getNode();
        }
        else if($this->_whichConfig == self::FLAG_ETC_CURRENT_CONFIG)
        {
            self::$_configs[$this->_whichConfig] = Mage::getConfig()->getNode();
        }
        else
        {
            self::$_configs[$this->_whichConfig] = Mage::getConfig()->loadModulesConfiguration($this->_whichConfig)->getNode();
        }
        
                    
        return self::$_configs[$this->_whichConfig];
    }
    
    protected function fail($message=false)
    {
        $this->_lastPassFail=false;
        $backtrace = debug_backtrace();
        $message .= ' FAILED: ' . $backtrace[1]['class'] . '::' . $backtrace[1]['function'] . ' at ' . $backtrace[0]['line'];
        $this->_allFail[] = $message;
    }
    
    const FLAG_ETC_BASE_CONFIG      = 'base';
    const FLAG_ETC_CURRENT_CONFIG   = 'current';        
    /**
    * Returns the name of the config (config.xml, system.xml, etc.) as a string
    *
    * Constants are used to return a config object that's not strictly loaded from files
    *
    * Override this in your lint class to test other config types
    */      
    protected function setWhichConfig()
    {
        return 'config.xml';
    }
}