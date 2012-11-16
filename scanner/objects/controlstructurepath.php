<?php

class Obj_ControlStructurePath {
    /**
     * Type of the control structure path
     * @var type 
     */
    protected $type;
    
    /**
     * The complete node of this controlstructure
     * @var PHPParser_Node 
     */
    protected $cond;
    
    protected $stmts;
    
    public function __construct($type, $stmts) {
        $this->setType($type);
        $this->setStmts($stmts);
    }
    
    /**
     * Set the type of this path
     * @param int $type
     */
    public function setType($type) {
        $this->type = $type;
    }
    
    /**
     * Get the type of this path
     * @return int
     */
    public function getType() {
        return $this->type;
    }
      
    
    /**
     * Set the statements that get executed when this condition is true
     * @param array $stmts
     */
    public function setStmts(array $stmts) {
        $this->stmts = $stmts;
    }
    
    /**
     * Get the statements that get executed, when this condition is true
     * @return type
     */
    public function getStmts() {
        return $this->stmts;
    }
      
    
    /**
     * Set the condition for this path to be executed
     * @param PHPParser_Node_Expr $cond
     */
    public function setCond(PHPParser_Node_Expr $cond) {
        $this->cond = $cond;
    }
    
    /**
     * Return the condition for this path to be executed
     * @return PHPParser_Node_Expr
     */
    public function getCond() {
        return $this->cond;
    }
      
    
    
}

?>
