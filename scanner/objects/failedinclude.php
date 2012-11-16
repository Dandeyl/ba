<?php

class Obj_FailedInclude {
    /**
     * Complete node that could not be resolved.
     * @var type 
     */
    public $include_node;
    
    
    public function __construct($node) {
        $this->include_node = $node;
    }
}