<?php
/**
 * An object of this class contains information about a function or method in the source code
 */
abstract class Obj_CodeSequenceInfo {
    /**
     * The expression. Used to execute later.
     * @var PHPParser_Node_Expr 
     */
    protected $phpparser_expr;
    
    
    
    /**
     * Does this code sequence return a safe value? Safe values are booleans, integers and floats.
     * @var bool 
     */
    protected $safe_return;
    
    /**
     * Can this function be executed without changing the environment. "Changing 
     * the environment"  is done by e.g. executing system functions, altering a database 
     * or changing files.
     * @var bool 
     */
    protected $executable;
    
    /**
     * Name of the functions to check if this function really is vulnerable. E.g.
     * preg_replace is just dangerous if the modifier "e" is passed. So we have to create a
     * function pluteus_check_vulnerable_preg_replace($search, $replace, $haystack) in which we
     * check the value of $search. 
     * If more than one function is given, all functions have to return false to signalise the
     * function is secure.
     * @var string[] 
     */
    protected $func_check_vulnerable;
    
    
    //----------------------------
    //--------- Methods ----------    
    
    /**
     * Set the PHPParser_Node_Expr, used to be executed later.
     * @param PHPParser_Node_Expr $expr
     */
    public function setExpression(PHPParser_Node_Expr $expr) {
        $this->phpparser_expr = $expr;
    }
    
    /**
     * Returns the PHPParser_Node_Expr. Can be executed using Scanner::prettyPrintExpr()
     * @return PHPParser_Node_Expr
     */
    public function getExpression() {
        return $this->phpparser_expr;
    }
    
    /**
     * Set wether the code sequence is executable
     * @param type $executable
     * @return type
     */
    public function setExecutable($executable) {
        
        $this->executable = (bool) $executable;
    }
    
    /**
     * Get wether the code sequence is executable
     * @param type $executable
     * @return type
     */
    public function isExecutable() {
        return (bool) $this->executable;
    }
    
    
    
    
    public function setSafeReturn($safe_return) {
        $this->safe_return = (bool) $safe_return;
    }
    
    public function getSafeReturn() {
        
    }
}