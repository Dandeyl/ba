<?php
/**
 * This class collects information about variables. It stops everytime a variable 
 * or constant is assigned.
 */
class NodeVisitor_Assignments extends PHPParser_NodeVisitorAbstract
{
    /**
     * This method writes the scanned variable into the varlist in class ScanInfo
     * @param PHPParser_Node $node
     */
    public function leaveNode(PHPParser_Node $node) { // use leaveNode to be able to parse sth like $node = ($p = 123); 
        $assignment_type = $this->getAssignmentType($node);
        
        // no assignment, continue
        if(!$assignment_type) {
            return;
        }
        
        $assignment_target = $this->getAssignmentTarget($node, $assignment_type);
        
        // variable
        if($assignment_target == Assignment::TargetVariable) {
            return $this->resolveVariable($node, $assignment_type);
        }
        // array
        elseif($assignment_target == Assignment::TargetArray) {
            $this->resolveArray($node);
        }
        
    }
    
    /**
     * Return the assignment type.
     * @param PHPParser_Node $node
     */
    protected function getAssignmentType(PHPParser_Node $node) {
        $class = get_class($node);
        switch($class) {
            case 'PHPParser_Node_Expr_Assign':           return Assignment::Assign; break;
            case 'PHPParser_Node_Expr_AssignBitwiseAnd': return Assignment::AssignBitwiseAnd; break;
            case 'PHPParser_Node_Expr_AssignBitwiseOr':  return Assignment::AssignBitwiseOr; break;
            case 'PHPParser_Node_Expr_AssignBitwiseXor': return Assignment::AssignBitwiseXor; break;
            case 'PHPParser_Node_Expr_AssignConcat':     return Assignment::AssignConcat; break;
            case 'PHPParser_Node_Expr_AssignDiv':        return Assignment::AssignDiv; break;
            case 'PHPParser_Node_Expr_AssignMinus':      return Assignment::AssignMinus; break;
            case 'PHPParser_Node_Expr_AssignMod':        return Assignment::AssignMod; break;
            case 'PHPParser_Node_Expr_AssignMul':        return Assignment::AssignMul; break;
            case 'PHPParser_Node_Expr_AssignPlus':       return Assignment::AssignPlus; break;
            case 'PHPParser_Node_Expr_AssignRef':        return Assignment::AssignRef; break;
            case 'PHPParser_Node_Expr_AssignShiftLeft':  return Assignment::AssignShiftLeft; break;
            case 'PHPParser_Node_Expr_AssignShiftRight': return Assignment::AssignShiftRight; break;
            case 'PHPParser_Node_Stmt_ClassConst':       return Assignment::ClassConstant; break;
            case 'PHPParser_Node_Stmt_Const':            return Assignment::Constant; break;
            case 'PHPParser_Node_Expr_FuncCall': 
                $name = Helper_NameResolver::resolve($node);
                if($name == "define") {
                    return Assignment::FuncDefine;
                }
                break;
            default: 
                return false; 
                break;
        }
    }
    /**
     * Is the current node a variable assignment
     */
    protected function getAssignmentTarget(PHPParser_Node $node, $assignment_type) {
        $assignments = array(
            Assignment::Assign,
            Assignment::AssignBitwiseAnd,
            Assignment::AssignBitwiseOr,
            Assignment::AssignBitwiseXor,
            Assignment::AssignConcat,
            Assignment::AssignDiv,
            Assignment::AssignMinus,
            Assignment::AssignMod,
            Assignment::AssignMul,
            Assignment::AssignPlus,
            Assignment::AssignRef,
            Assignment::AssignShiftLeft,
            Assignment::AssignShiftRight,
        );
        
        if(in_array($assignment_type, $assignments)) 
        {
            // $var = ..
            if($node->var instanceof PHPParser_Node_Expr_Variable) {
                return Assignment::TargetVariable;
            }
            
            // $var["index"] = ..
            if($node->var instanceof PHPParser_Node_Expr_ArrayDimFetch) {
                return Assignment::TargetArray;
            }
            
            // $obj->property = ...
            elseif ($node->var instanceof PHPParser_Node_Expr_PropertyFetch) {
                return Assignment::TargetProperty;
            }
            
            // Classname::$var = ..
            elseif ($node->var instanceof PHPParser_Node_Expr_StaticPropertyFetch) {
                return Assignment::TargetStaticProperty;
            }
        }
        
        // define();
        else if($assignment_type == Assignment::FuncDefine) {
            $name = Helper_NameResolver::resolve($node);
            if($name == "define") {
                return Assignment::TargetConstant;
            }
        }
        
        // class name { const Bla = ... }
        else if($assignment == Assignment::ClassConstant) {
            return Assignment::TargetClassConstant;
        }
        
        return false;
    }
    
 
    
    
    
    /**
     * Check if the name of a property, variable etc. is valid
     * @param type $name
     * @param type $type
     * @return boolean
     */
    private function isValidName($name, $type) {
        if($type == Assignment::TargetVariable) {
            if(preg_match('/^\$[a-z_][a-z0-9_]*?$/i', $name)) {
                return true;
            }
        }
        return false;
    }
    
    ////////////////////////////////////////////////////////////////
    ///   Normal variable
    ////////////////////////////////////////////////////////////////
    
    
    /**
     * Get name and content of the variable.
     * @param PHPParser_Node $node
     * @return type
     * @throws Exception
     */
    protected function resolveVariable(PHPParser_Node $node, $assignment_type) {
        // get resolved name
        $name    = Helper_NameResolver::resolve($node->var);
       
        // check resolved name
        if(!$this->isValidName($name, Assignment::TargetVariable)) {
            if($name == false) {
                // could not resolve variable name
                throw new Exception("NodeVisitor_Assignment: Can't resolve variable name");
            }
            else {
                // could not resolve variable name
                throw new Exception("NodeVisitor_Assignment: Variable name is not valid.");
            }
            return;
        }
        
        // Value assignment
        if($assignment_type != Assignment::AssignRef) {
            $resolve = Helper_ExpressionResolver::resolve($node->expr);
            $this->addVariable($name, $resolve, $assignment_type, $node);
        }
        
        // reference assignment
        else {
            $reference = Helper_NameResolver::resolve($node->expr);
            $this->addVariableRef($name, $reference, $node);
        }
        return $node->var;
    }
    
    
    
    /**
     * Adds a variable to the varlist.
     * @param string $name
     * @param Obj_Resolved $resolved
     * @param int $assignment_type
     * @param PHPParser_Node $node
     */
    private function addVariable($name, Obj_Resolved $resolved, $assignment_type, $node) {
        $vari = new Obj_Variable($name);
        $vari->setScope(ScanInfo::getScope());
        $vari->setSecureFor($resolved->getSecuredFor());
        $vari->setAssignmentNode($node);
        
        if($assignment_type == Assignment::AssignConcat) {
            $exst_var = ScanInfo::findVar($name);
            $vari->setUserDefined($resolved->isUserDefined() || $exst_var->isUserDefined());
            $vari->setValue($exst_var->getValue().$resolved->getValue());
        }
        else {
            $vari->setUserDefined($resolved->isUserDefined());
            $vari->setValue($resolved->getValue(), $assignment_type);
        }
        
        ScanInfo::addVar($vari);
    }
    
    /**
     * Adds a variable to the varlist that is reference to another variable.
     * @param string $name
     * @param string $reference Name of the reference variable
     * @param PHPParser_Node Node of the assignment in the tree
     */
    private function addVariableRef($name, $reference, $node) {
        $ref_vari = ScanInfo::findVar($reference);
        
        
        $vari = new Obj_Variable($name);
        $vari->setAssignmentNode($node);
        $vari->setScope(ScanInfo::getScope());
        
        if($ref_vari) { 
            $vari->setReferenceTo($ref_vari);
        }
        else {
            $vari->setReferenceTo($reference);
            ScanInfo::addWarning(Warning::ReferenceVariableUndefined, $node);
        }
        ScanInfo::addVar($vari, $node);
    }
    
    
    ////////////////////////////////////////////////////////////////
    ///   Array
    ////////////////////////////////////////////////////////////////
    
    
    
    
    
    
    
    
    
    
    
    
    
      
}