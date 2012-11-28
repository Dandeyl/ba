<?php

class PHPParser_NodeTraverser
{
    /**
     * @var PHPParser_NodeVisitor[] Active visitors
     */
    protected $visitors;
    
    protected $all_visitors;
    
    protected $visitorIndex;
    
    protected $hasfinished=false;
    
    /**
     * Constructs a node traverser.
     */
    public function __construct() {
        $this->visitors = array();
    }

    /**
     * Adds a visitor.
     *
     * @param PHPParser_NodeVisitor $visitor Visitor to add
     */
    public function addVisitor(PHPParser_NodeVisitor $visitor) {
        $this->all_visitors[] = $visitor;
        $this->visitors = $this->all_visitors;
        return (count($this->all_visitors)-1);
    }
    
    
    /**
     * Adds a visitor at a given position.
     * 
     * @param PHPParser_NodeVisitor $visitor
     * @param int $pos
     */
    public function addVisitorAt(PHPParser_NodeVisitor $visitor, $pos) {
        array_splice($this->all_visitors, $pos, 0, array($visitor));
        $this->visitors = $this->all_visitors;
    }
    
    public function removeVisitor($class_name) {
        foreach($this->all_visitors as $key => $visitor) {
            $class = get_class($visitor);
            if($class == $class_name) {
                array_splice($this->all_visitors, $key, 1);
            }
        }
        $this->visitors = $this->all_visitors;
        $this->visitorIndex = 0;
    }
    
    
    /**
     * Set the visitors to be active. If an empty array is passed, all visitors
     * will be made active
     * @param array $indexes
     */
    public function setActiveVisitors($indexes) {
        if($indexes === null) {
            $this->visitors = array();
        }
        elseif(empty($indexes)) {
            $this->visitors = $this->all_visitors;
        }
        else {
            $visitors = array();
            foreach($indexes as $idx) {
                $visitors[] = $this->all_visitors[$idx];
            }
            $this->visitors = $visitors;
        }
        $this->visitorIndex = 0;
    }
    

    /**
     * Traverses an array of nodes using the registered visitors.
     *
     * @param PHPParser_Node[] $nodes Array of nodes
     *
     * @return PHPParser_Node[] Traversed array of nodes
     */
    public function traverse(array $nodes) {
        $this->hasfinished = false;
        foreach ($this->visitors as $visitor) {
            if (null !== $return = $visitor->beforeTraverse($nodes)) {
                $nodes = $return;
            }
        }

        $nodes = $this->traverseArray($nodes);

        $this->hasfinished = true;
        foreach ($this->visitors as $visitor) {
            if (null !== $return = $visitor->afterTraverse($nodes)) {
                $nodes = $return;
            }
        }
       
        return $nodes;
    }
    
   
    protected function traverseNode(PHPParser_Node $node) {
        $node = clone $node;
        
        foreach ($node->getSubNodeNames() as $name) {
            $subNode =& $node->$name;

            if (is_array($subNode)) {
                $subNode = $this->traverseArray($subNode);
            } elseif ($subNode instanceof PHPParser_Node) {
                foreach ($this->visitors as $visitor) {
                    if (null !== $return = $visitor->enterNode($subNode)) {
                        $subNode = $return;
                    }
                    
                }
                $subNode = $this->traverseNode($subNode);

                foreach ($this->visitors as $visitor) {
                    if (null !== $return = $visitor->leaveNode($subNode)) {
                        $subNode = $return;
                    }
                }
            }
        }
        return $node;
    }

    protected function traverseArray(array $nodes) {
        $doNodes = array();
        reset($nodes);
        
        while(current($nodes)) {
            
            $i    = key($nodes);
            $node = &$nodes[$i];
            
            if (is_array($node)) {
                $node = $this->traverseArray($node);
            } elseif ($node instanceof PHPParser_Node) {
                // ENTER NODE for each visitor
                $this->visitorIndex = 0;
                $enterNode          = true;
                
                while($enterNode) {
                    $enterNode = false;
                    // surpress out of bounds
                    while (($visitor = @$this->visitors[$this->visitorIndex])) {
                        $return = $visitor->enterNode($node);
                        if (is_array($return)) {
                            $doNodes[] = array($i, $return);
                            break;
                        }
                        elseif (null !== $return) {
                            $node = $return;
                        }
                        elseif(!empty($this->node_replacements)) {
                            $doNodes[] = array($i, $this->node_replacements);
                        }
                        $this->visitorIndex++;
                    }
                    if (!empty($doNodes)) {
                        while (list($i, $replace) = array_pop($doNodes)) {
                            array_splice($nodes, $i, 1, $replace);
                        }
                        $node = current($nodes);
                        if($node !== false) {
                            $enterNode = true;
                            $this->visitorIndex = 0;
                        }
                        
                    }
                }
                
                if($node === false) {
                    continue;
                }
                
                $node = $this->traverseNode($node);

                
                // LEAVE NODE for each visitor
                foreach ($this->visitors as $visitor) {
                    $return = $visitor->leaveNode($node);

                    if (false === $return) {
                        $doNodes[] = array($i, array());
                        break;
                    } elseif (is_array($return)) {
                        $doNodes[] = array($i, $return);
                        break;
                    } elseif (null !== $return) {
                        $node = $return;
                    }
                }
            }
            next($nodes);
        }

        if (!empty($doNodes)) {
            while (list($i, $replace) = array_pop($doNodes)) {
                array_splice($nodes, $i, 1, $replace);
            }
        }

        return $nodes;
    }
    
    public function hasFinished($set=null) {
        if($set === null) 
            return (bool) $this->hasfinished;
        
        $this->hasfinished = (bool) $set;
    }
}