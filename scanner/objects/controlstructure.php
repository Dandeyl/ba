<?php

class Obj_ControlStructure {
    // Type constants
    const STMT_IF = 1;
    const STMT_ELSEIF = 2;
    const STMT_ELSE = 3;
    const STMT_SWITCH = 4;
    const STMT_CASE = 4;
    const STMT_WHILE = 6;
    const STMT_DO = 7;
    const STMT_FOR = 8;
    const STMT_FOREACH = 9;
    const EXPR_TERNARY = 10;
    const EXPR_LOGICAL_OR = 11;
    
    /**
     * Type of the control structure. (IF/SWITCH/WHILE/...)
     * @var type 
     */
    protected $type;
    
    /**
     * Complete node of this controlstructure
     * @var PHPParser_Node 
     */
    protected $node;
    
    /**
     * Paths this controlstructure has
     * @var Obj_ControlstructurePath[]
     */
    protected $paths;
    
    /**
     * How many paths this structure has. Equals count($this->paths);
     * @var int 
     */
    protected $num_paths;
    
    /**
     * What path we are in now.
     * @var int 
     */
    protected $num_path_current;
    
    /**
     * Filestack to get to this control structure
     * @var Obj_Filetree[] 
     */
    protected $filestack;
    
    /**
     * ScanInfo to be restored 
     * @var type
     */
    protected $scaninfo_export;
    
    
    public function __construct($type, PHPParser_Node $node) {
        $this->setType($type);
        $this->setNode($node);
        $this->num_path_current = 0;
    }
    
    
    public function setType($type) {
        $this->type = $type;
    }
    public function getType() {
        return $this->type;
    }
      
    public function setNode(PHPParser_Node $node) {
        $this->node = $node;
    }
    
    /**
     * The the full node of this controlstructure
     * @return PHPParser_Node
     */
    public function getNode() {
        return $this->node;
    }
    
    /**
     * Set subnodes of this controlstruct. Subnodes are 
     * @param Obj_ControlStructurePath $paths
     */
    public function setPaths(array $paths) {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }
    
    public function addPath(Obj_ControlStructurePath $path) {
        $this->paths[] = $path;
    }
    
    public function getPath($num = 0) {
        return $this->paths[$num];
    }
    
    /**
     * Remove current path
     */
    public function removePath() {
        $key = key($this->paths);
        unset($this->paths[$key]);
    }
    /**
     * Get the file this node was defined in.
     * @return string
     */
    public function getFile() {
        return $this->node->getAttribute("file");
    }
    
    
    /**
     * Get the line this node was defined in.
     * @return int
     */
    public function getLine() {
        return $this->node->getLine();
    }
    
    
    /**
     * Returns the currently active path in the control structure.
     * @return int Path that is currently taken.
     */
    public function getCurrentPathNumber() {
        return $this->num_path_current;
    }
    
    /**
     * Increase the path number.
     * @return int increased path number
     */
    public function incCurrentPathNumber() {
        return ++$this->num_path_current;
    }
    
    /**
     * Get the number of paths available in this construct
     * @return int
     */
    public function getNumPaths() {
        return count($this->paths);
    }
    
    
    public function setFileStack(array $filestack) {
        $this->filestack = $filestack;
    }
    
    public function getFileStack() {
        return $this->filestack;
    }
    
    public function setScanInfoExport($export) {
        $this->scaninfo_export = $export;
    }
    
    public function getScanInfoExport() {
        return $this->scaninfo_export;
    }
    
}

?>
