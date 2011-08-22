<?php

//     Copyright (c) 2010 Kristof Ringleff
//     
//     Permission is hereby granted, free of charge, to any person obtaining a copy
//     of this software and associated documentation files (the "Software"), to deal
//     in the Software without restriction, including without limitation the rights
//     to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
//     copies of the Software, and to permit persons to whom the Software is
//     furnished to do so, subject to the following conditions:
//     
//     The above copyright notice and this permission notice shall be included in
//     all copies or substantial portions of the Software.
//     
//     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//     IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//     FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//     AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//     LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
//     OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
//     THE SOFTWARE.

/*
 * @category    Alanstormdotcom
 * @package     Alanstormdotcom_Contributedlints
 * @author      Kristof Ringleff
 * @copyright   Copyright (c) 2010 Fooman Ltd (http://www.fooman.co.nz)
 * @license     see above
 */

class Alanstormdotcom_Contributedlints_Helper_Autoload extends Varien_Autoload
{

    /**
     * Need to extend since we don't want to include the class file just want the name
     * Load class source code
     *
     * @param string $class
     */
    public function getClassFile($class)
    {
        $found = false;
        if ($this->_collectClasses) 
        {
            $this->_arrLoadedClasses[self::$_scope][] = $class;
        }
        
        if ($this->_isIncludePathDefined) 
        {
            $classFile = $this->_collectPath.DS.$class;
        } 
        else 
        {
            $classFile = str_replace(' ', DIRECTORY_SEPARATOR, ucwords(str_replace('_', ' ', $class)));
        }
        $classFile .= '.php';
        
        foreach (explode(PS, get_include_path()) as $inclPath) 
        {
            if (file_exists($inclPath.DS.$classFile) && !$found) 
            {
                $classFile = $inclPath.DS.$classFile;
                $found = true;
            }
        }
        
        if ($found)
        {
            return file_get_contents($classFile);
        } 
        else 
        {
           return '';
        }
    }
}