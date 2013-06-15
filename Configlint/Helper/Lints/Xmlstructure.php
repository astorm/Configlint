<?php
//  Copyright (c) 2010 - 2013 Pulse Storm LLC
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

class Alanstormdotcom_Configlint_Helper_Lints_Xmlstructure extends Alanstormdotcom_Configlint_Helper_Lints_Abstract
{           
    protected function setWhichConfig()
    {
        return 'config.xml';
    }   

    /**
    * Tests that all the expected top level xml nodes are in place
    *
    * Doesn't impose that only nodes xyz be in place, it just makes sure
    * the known nodes ARE there
    */      
    public function lintTestTopLevel($config)
    {
        $expected_top   = array('modules','global','frontend','adminhtml','install','default','stores','admin','websites','crontab');
        $xml = simplexml_load_string($config->asXML());
        $found_top      = array();
        foreach($xml as $item)
        {
            $found_top[] = $item->getName();
        }

        //if one of the expected modules is missing, fail
        foreach($expected_top as $node)
        {
            if(!in_array($node, $found_top))
            {
                $this->fail('Could not find [&lt;' . $node . '/&gt;] at the top level. (in ' . 
                __METHOD__ . ' near line ' .
                __LINE__ . 
                ')');
            }
        }
    }   

    /**
    * Classes in configs should be one of four types,
    * Models, Controllers, Blocks, Helpers
    */
    public function lintClassType($config)
    {
        $allowed = array('controller','model','block','helper');
        $nodes = $config->xPath('//class');     
        $errors = array();
        foreach($nodes as $node)
        {
            $str_node = (string) $node;
            if(strpos($str_node, '/') === false && strpos($str_node, '_') !== false)
            {
                $parts = preg_split('{_}',$str_node,4);                 
                if(array_key_exists(2, $parts) && !in_array(strToLower($parts[2]), $allowed))
                {           
                    $errors[] = "Invalid Type [$parts[2]] detected in class [$str_node]";
                }
            }
        }
        if(count($errors) >0)
        {
            $this->fail(implode("\n", $errors));
        }
        
    }
    
    /**
    * Tests that all classes are cased properly.  
    *       
    * This helps avoid __autoload problems when working 
    * locally on a case insensatie system
    */      
    public function lintClassCase($config)
    {
        $nodes = $config->xPath('//class');         
        $errors = array();
        foreach($nodes as $node)
        {
            $str_node = (string) $node;
            if(strpos($str_node, '/') !== false)
            {
                if($str_node != strToLower($str_node))
                {
                    $errors[] = 'URI ['.$str_node.'] must be all lowercase;'; 
                }
            }
            else if(strpos($str_node, '_') !== false)
            {
                $parts = preg_split('{_}',$str_node,4);
                foreach($parts as $part)
                {
                    if(ucwords($part) != $part)
                    {
                        $errors[] = "Class [$str_node] does not have proper casing. Each_Word_Must_Be_Leading_Cased.";
                    }
                }
            }
            else
            {
                $errors[] = 'Class ['.$str_node.'] doesn\'t loook like a class'; 
            }
        }
        
        if(count($errors) > 0)
        {
            $this->fail(implode("\n", $errors));
        }
    }
}