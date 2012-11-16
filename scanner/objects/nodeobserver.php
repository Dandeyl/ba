<?php

class NodeObserver extends Obj_Function {
    /**
     * Node that started the observer
     * @var PHPParser_Node
     */
    protected $node;
    
    
    public function __construct($monitor, PHPParser_Node $node_identifier) {
        $this->node = $node_identifier;
    }
}