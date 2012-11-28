<?php

class Obj_File {
    protected $path;
    
    protected $line;
    
    protected $call_node;
    
    public function __construct($path, $node = null) {
        if(!file_exists($path)) {
            throw new Exception("File does not exist: ".$path.".\n");
        }
        
        
        
        $this->path  = $path;
        $this->call_node = $node; 
        $this->_twigs = array();
        $this->_pointer = -1;
        $this->_controlstructs = array();
    }
}