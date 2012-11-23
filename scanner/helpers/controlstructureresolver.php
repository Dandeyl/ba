<?php
/**
 * Collects information about a control structure.
 */

class Helper_ControlStructureResolver {
    /**
     *
     * @var Obj_ControlStructure 
     */
    protected $struct;
    protected $conditions;
    
    
    protected function __construct(PHPParser_Node $node) {
        $type = NodeVisitor_ControlStructure::isControlStructure($node);
        
        $struct = &$this->struct;
        $struct = new Obj_ControlStructure($type, $node);
        $this->conditions = array();
        return $this;
    }
    
    
    protected function resolveIfStmt() {
        $node = $this->struct->getNode();
        $this->struct->setType(Obj_ControlStructure::STMT_IF);
        $conditions = &$this->conditions;
        
        // ----  IF                                               merge condition and stmts (
        $conditions[] = $node->cond;
        // check if condition is always true
        if($this->addPathInIfStmt(Obj_ControlStructure::STMT_IF, $node->cond, $node->stmts) === true) {
            return;
        }
        
        /*if(!$resolve->isUserDefined() 
           && ((bool) $resolve->getValue()) === false) {
            // this path never gets executed (not user defined and false)
        }
        else {
            $struct_path  = new Obj_ControlStructurePath(Obj_ControlStructure::STMT_IF, array_merge($conditions, $node->stmts));
            $struct_path->setCond($node->cond);
            
            if($resolve->isUserDefined()) {
                $paths[] = $struct_path;
            }
            elseif(((bool) $resolve->getValue()) === true) {
                $paths[] = $struct_path;
                $this->struct->setPaths($paths);
                return;
            }
        }*/
        
        // ------ ELSEIFs
        foreach((array) $node->elseifs as $elseif) {
            $conditions[]= $elseif->cond;
            if($this->addPathInIfStmt(Obj_ControlStructure::STMT_ELSEIF, $elseif->cond, $elseif->stmts) === true) {
                return;
            }
        }
        
        // ------ ELSE
        $struct_path = new Obj_ControlStructurePath(Obj_ControlStructure::STMT_ELSE, $node->else ? $node->else->stmts : array());
        $struct_path->setCond($node->cond);
        $this->struct->addPath($struct_path);
    }
    
    /**
     * Checks if the path has to be gone. Adds it to the structure 
     * @param int $path_type Type of the path the gets added (IF/ELSEIF...)
     * @param type $condition Condition for this path to be executed
     * @param array $stmts Statements that get executed if the condition can be true
     * @return bool|null Returns true if the path was entered and the condition is not defined by the user
     *                   Null if the condition is defined by the user (i.e. we don't know if it has to be entered or not)
     *                   False if it never gets entered using the current variable environment.
     */
    protected function addPathInIfStmt($path_type, $condition, array $stmts) {
        // resolve the expression
        $resolve = Helper_ExpressionResolver::resolve($condition);
        
        if(!$resolve->isUserDefined() 
           && ((bool) $resolve->getValue()) === false) {
            return false;
        }
        else {
            $struct_path  = new Obj_ControlStructurePath($path_type, array_merge($this->conditions, $stmts));
            $struct_path->setCond($condition);
            
            if($resolve->isUserDefined()) {
                $this->struct->addPath($struct_path);
                return null;
            }
            elseif(((bool) $resolve->getValue()) === true) {
                $this->struct->addPath($struct_path);
                return true;
            }
        }
    }
    
    protected function resolveTernaryExpr() {
        $node = $this->struct->getNode();
        $this->struct->setType(Obj_ControlStructure::EXPR_TERNARY);
        
        $paths = array();
        
        // if
        $if = ($node->if === null) ? $node->cond : $node->if;
        $struct_path  = new Obj_ControlStructurePath(Obj_ControlStructure::STMT_IF, $if);
        $struct_path->setCond($node->cond);
        $paths[] = $struct_path; 
        
        // else
        $struct_path = new Obj_ControlStructurePath(Obj_ControlStructure::STMT_ELSE, $node->else);
        $struct_path->setCond($node->cond);
        $paths[] = $struct_path;
        
        $this->struct->setPaths($paths);
    }
    
    protected function getStruct() {
        return $this->struct;
        if($test == $test) {if($test == $test) {  echo "j";} echo "lo";}
    }
    
    
    /**
     * @param PHPParser_Node $node
     * @return Obj_ControlStructure
     */
    public static function resolveIf(PHPParser_Node_Stmt_If $node) {
        $hlper = new Helper_ControlStructureResolver($node);
        $hlper->resolveIfStmt();
        return $hlper->getStruct();
    }
    
    public static function resolveTernary(PHPParser_Node_Expr_Ternary $node) {
        $hlper = new Helper_ControlStructureResolver($node);
        $hlper->resolveTernaryExpr();
        return $hlper->getStruct();
    }
}