<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//  
//  Copyright (c) 2004-2005 Laurent Bedubourg
//  
//  This library is free software; you can redistribute it and/or
//  modify it under the terms of the GNU Lesser General Public
//  License as published by the Free Software Foundation; either
//  version 2.1 of the License, or (at your option) any later version.
//  
//  This library is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  Lesser General Public License for more details.
//  
//  You should have received a copy of the GNU Lesser General Public
//  License along with this library; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//  
//  Authors: Laurent Bedubourg <lbedubourg@motion-twin.com>
//  

require_once 'PHPTAL/Dom/Defs.php';
require_once 'PHPTAL/Php/CodeWriter.php';
require_once 'PHPTAL/Php/Attribute.php';

/**
 * Document node abstract class.
 *
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
abstract class PHPTAL_Node
{
    public function __construct(PHPTAL_Dom_Parser $parser)
    {
        $this->_parser = $parser;
        $this->_line = $parser->getLineNumber();
    }

    public function getSourceLine()
    {
        return $this->_line;
    }
    
    public function getSourceFile()
    {
        return $this->_parser->getSourceFile();
    }

    private $_parser;
    private $_line;
}

/**
 * Node container.
 * 
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class PHPTAL_NodeTree extends PHPTAL_Node
{
    public function __construct(PHPTAL_Dom_Parser $parser)
    {
        parent::__construct($parser);
        $this->_children = array();
    }

    public function addChild(PHPTAL_Node $node)
    {
        array_push($this->_children, $node);
    }
    
    public function &getChildren()
    {
        return $this->_children;
    }

    protected $_children;
}

/**
 * Document Tag representation.
 *
 * This is the main class used by PHPTAL because TAL is a Template Attribute
 * Language, other Node kinds are (usefull) toys.
 *
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class PHPTAL_NodeElement extends PHPTAL_NodeTree
{
    private $name;
    public $attributes = array();

    public function __construct(PHPTAL_Dom_Parser $parser, $name, $attributes)
    {
        parent::__construct($parser);
        $this->name = $name;
        $this->attributes = $attributes;
        $this->_xmlns = $parser->getXmlnsState();
        $this->xmlns = $this->_xmlns;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getXmlnsState()
    {
        return $this->_xmlns;
    }

    /** Returns true if the element contains specified PHPTAL attribute. */
    public function hasAttribute($name)
    {
        $ns = $this->getNodePrefix();
        foreach ($this->attributes as $key=>$value){
            if ($this->_xmlns->unAliasAttribute($key) == $name){
                return true;
            }
            if ($ns && $this->_xmlns->unAliasAttribute("$ns:$key") == $name){
                return true;
            }
        }
        return false;
    }

    /** Returns the value of specified PHPTAL attribute. */
    public function getAttribute($name)
    {
        $ns = $this->getNodePrefix();
        
        foreach ($this->attributes as $key=>$value){
            if ($this->_xmlns->unAliasAttribute($key) == $name){
                return $value;
            }
            if ($ns && $this->_xmlns->unAliasAttribute("$ns:$key") == $name){
                return $value;
            }
        }
        return false;
    }

    /** 
     * Returns true if this element or one of its PHPTAL attributes has some
     * content to print (an empty text node child does not count).
     */
    public function hasRealContent()
    {
        if (count($this->_children) == 0)
            return false;

        if (count($this->_children) == 1){
            $child = $this->_children[0];
            if ($child instanceOf PHPTAL_NodeText && $child->value == ''){
                return false;
            }
        }

        return true;
    }

    private function getNodePrefix()
    {
        $result = false;
        if (preg_match('/^(.*?):block$/', $this->name, $m)){
            list(,$result) = $m;
        }
        return $result;
    }
    
    private function hasContent()
    {
        return count($this->_children) > 0;
    }

    /** 
     * XMLNS aliases propagated from parent nodes and defined by this node
     * attributes.
     */
    protected $_xmlns;
}

/**
 * Document text data representation.
 */
class PHPTAL_NodeText extends PHPTAL_Node
{
    public $value;

    public function __construct(PHPTAL_Dom_Parser $parser, $data)
    {
        parent::__construct($parser);
        $this->value = $data;
    }
}

/**
 * Comment, preprocessor, etc... representation.
 * 
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class PHPTAL_NodeSpecific extends PHPTAL_Node
{
    public $value;

    public function __construct(PHPTAL_Dom_Parser $parser, $data)
    {
        parent::__construct($parser);
        $this->value = $data;
    }
}

/**
 * Document doctype representation.
 * 
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class PHPTAL_NodeDoctype extends PHPTAL_Node
{
    public $value;

    public function __construct(PHPTAL_Dom_Parser $parser, $data)
    {
        parent::__construct($parser);
        $this->value = $data;
    }
}

/**
 * XML declaration node.
 *
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class PHPTAL_NodeXmlDeclaration extends PHPTAL_Node
{
    public $value;

    public function __construct(PHPTAL_Dom_Parser $parser, $data)
    {
        parent::__construct($parser);
        $this->value = $data;
    }
}

?>
