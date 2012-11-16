<?php
/**
 * This nodevisitor detects scope and line changes and passes them to the ScanInfo class which holds all the information about the scanning process.
 */
class NodeVisitor_Scope extends PHPParser_NodeVisitorAbstract {
    protected $previous_scope=null;    
    
    /**
     * Check if the active scope has to be changed because of the current node. Either false, or the full 
     * name of the scope is returned.
     * @param PHPParser_Node $node
     * @return string|false
     */
    private function getFullScopeName(PHPParser_Node $node) {
        if($node instanceof PHPParser_Node_Stmt_Function) {
            return 'Function#'. (string) Helper_NameResolver::resolve($node);
        }
        
        /*else if($node instanceof PHPParser_Node_Stmt_Namespace) {
            return 'Namespace#'. (string) $node->name;
        }*/
        
        else if($node instanceof PHPParser_Node_Stmt_Class) {
            return 'Class#'.(string) Helper_NameResolver::resolve($node);
        }
        else if($node instanceof PHPParser_Node_Stmt_Interface) {
            return 'Interface#'.(string) Helper_NameResolver::resolve($node);
        }
        
        else if($node instanceof PHPParser_Node_Stmt_ClassMethod) {
            // get class name
            $class_tokens = explode('#', ScanInfo::getScope());
            $class = $class_tokens[1]; // classname
            
            // is static?
            if($node->type & PHPParser_Node_Stmt_Class::MODIFIER_STATIC) {
                $type = 'StaticMethod';
            }
            else {
                $type = "Method";
            }
            
            return $type.'#'.$class.'#'.(string) $node->name;
        }
        
        // no scope change
        return false;
    }
    
    
    /**
     * This method checks if the current scope has to be changed. The Scope has to be changed whenever namespace-, function-, method- or class- nodes get called. 
     * @param PHPParser_Node $node
     */
    public function enterNode(PHPParser_Node $node) {
        // check if scope has to be changed
        $scopename = $this->getFullScopeName($node);
        
        // if it has to be changed
        if($scopename !== false) {
            $this->setScope($scopename);
        }
    }
    
    
    /**
     * Resets the scope
     * @param PHPParser_Node $node
     */
    public function leaveNode(PHPParser_Node $node) {
        $scopename = $this->getFullScopeName($node);
        
        if($scopename !== false) {
            ScanInfo::setScope((string) $this->previous_scope[$scopename]);
        }
        
    }
    
    /**
     * Temporarily saves the current scope and sets a new one
     */
    private function setScope($scopename) {
        // buffer current scope
        $this->previous_scope[$scopename] = ScanInfo::getScope();
        ScanInfo::setScope($scopename);
    }
}
?>
