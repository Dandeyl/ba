<?php
/**
 * Information about variables and constants. 
 */
class Obj_Variable {
    /**
     * Name of the variable or constant. Must contain only alphanumeric characters and the underscore, 
     * variables must start with one "$". 
     * @var string 
     */
    protected $name;
    
    /**
     * The node of this variables assignment. This is used to be able to keep track of the change of the variable.
     * @var PHPParser_Node 
     */
    protected $assignment_node;
    
    /**
     * List of scopes where the variable is visible. If it's an empty string it is visible in global scope. 
     * @var string
     */
    protected $scope;
    
    /**
     * Is this variable visible in every scope?
     * @var bool 
     */
    protected $supergobal;
    /**
     * If it's possible to determine, this property contains the value of the variable.
     * @var type 
     */
    protected $value;
    
    /**
     * The type of the variable. Currently supported:
     *   - string
     *   - int
     *   - float
     *   - bool
     *   - array
     *   - mixed
     *   - null
     * @var string 
     */
    protected $type;
    
    /**
     * If the variable is static, it won't get deleted when leaving a function
     * @var bool 
     */
    protected $static;
    
    /**
     * If this variable is a reference to another variable, this property contains the name fo the referenced variable.
     */
    protected $reference_to;
    
    /**
     * Can the user define the content of this variable. => INSECURE
     * @var bool
     */
    protected $user_defined;
    
    /**
     * For which attacks this variable got secured.
     * @var array
     */
    protected $secured_for;
    
    /**
     * If the variable gets overwritten the old version will be stored here.
     * @var Obj_Variable[] 
     */
    protected $history;
    
    
    public function __construct($name=null) {
        $this->_setup($name);
    }
    
    /**
     * Used in constructor and method update
     * @param string $name
     */
    protected function _setup($name=null) {
        $this->scope = array();
        $this->value = null;
        $this->type  = 'null';
        $this->static = false;
        $this->secured_for  = array();
        $this->user_defined = false;
        $this->reference_to = &$this;
        $this->history = array();
        
        if($name) {
            $this->setName($name);
        }
    }
    
    /**
     * Set the variable this one references to. If the referenced variable is not yet set,
     * specify the name of this variable
     * @param Obj_Variable|string $variable
     */
    public function setReferenceTo(&$variable) {
        $this->reference_to = &$variable;
    }
    /**
     * Get the variable this one references to.
     * @return Obj_Variable The variable this one references to. Returns itself if it does not have a reference.
     */
    public function getReferenceTo() {
        if($this->reference_to === $this) return $this;
        return $this->reference_to;
    }
    
    /**
     * Set the name of the variable
     * @param string $name
     */
    public function setName($name) {
        $this->name = (string) $name;
    }
    
    /**
     * Get the name of the variable
     * @return type
     */
    public function getName() {
        return $this->name;
    }
    
    // Node
    /**
     * Sets the node where the assigment was made.
     * @param PHPParser_Node $node
     */
    public function setAssignmentNode(PHPParser_Node $node) {
        $this->getReferenceTo()->assignment_node = $node;
    }
    
    /**
     * Get the node where the assignment was made.
     * @return PHPParser_Node
     */
    public function getAssignmentNode() {
        return $this->getReferenceTo()->assignment_node;
    }
    
    
    
    // Scope
    public function setScope($scope) {
        if(is_array($scope)) {
            $this->scope = $scope;
        }
        else if(is_string($scope)) {
            $this->scope = array($scope);
        }
        else {
            throw new Exception("Variable: Illegal scope passed.");
        }
    }
        
    /**
     * Sets if this variable is visible in every scope
     * @param bool $superglobal
     */
    public function setSuperGlobal($superglobal) {
        $this->supergobal = (bool) $superglobal;
    }
    
    /**
     * 
     * @return type
     */
    public function isSuperGlobal() {
        return (bool) $this->supergobal;
    }
    
    /**
     * Get the variables scopes.
     * @return array
     */
    public function getScope() {
        return $this->scope; 
    }
    
    /**
     * Is the variable visible in the given scope?
     * @param string $scope
     * @return boolean
     */
    public function hasScope($scope) {
        if($scope == null) { // null or empty string
            if(empty($this->scope)) {
                return true;
            }
        }
        elseif($scope == $this->scope) {
            return true;
        }
        return false;
    }
    
    // Value
    public function setValue($value, $assignment_type =  Assignment::Assign) {
        $newval = $this->getValue();
        
        switch($assignment_type) {
            case Assignment::Assign:
                $newval = $value;
                break;
            case Assignment::AssignBitwiseAnd:
                $newval &= $value;
                break;
            case Assignment::AssignBitwiseOr:
                $newval |= $value;
                break;
            case Assignment::AssignBitwiseXor:
                $newval ^= $value;
                break;
            case Assignment::AssignConcat:
                $newval .= $value;
                break;
            case Assignment::AssignDiv:
                $newval /= $value;
                break;
            case Assignment::AssignMinus:
                $newval -= $value;
                break;
            case Assignment::AssignMod:
                $newval %= $value;
                break;
            case Assignment::AssignMul:
                $newval *= $value;
                break;
            case Assignment::AssignPlus:
                $newval += $value;
                break;
            /*case Assignment::AssignRef:
                $var = ScanInfo::findVar($value);
                if($var instanceof Obj_Variable) {
                    $this->reference_to = &$var;
                }
                break;*/
            case Assignment::AssignShiftLeft:
                $newval <<= $value;
                break;
            case Assignment::AssignShiftRight:
                $newval >>= $value;
                break;
        }
        
        $this->getReferenceTo()->value = $newval;
        $this->getReferenceTo()->type  = gettype($newval); 
    }
    
    
    public function getValue() {
        return $this->getReferenceTo()->value; 
    }
    
    
    public function getType() {
        return $this->getReferenceTo()->type;
    }
    
    /**
     * Set if the variable is user defined
     * @param bool $userdefined
     */
    public function setUserDefined($userdefined) {
        $this->getReferenceTo()->user_defined = (bool) $userdefined;
    }
    
    /**
     * Get if the variable is user defined
     * @return type
     */
    public function isUserDefined() {
        return $this->getReferenceTo()->user_defined; 
    }
    
    
    
    public function setStatic($static) {
        $this->static = (bool) $static;
    }
    
    public function isStatic() {
        return $this->static;
    }
    
    // is secured
    public function setSecureFor(array $secured_for) {
        $this->getReferenceTo()->secured_for = $secured_for;
    }
    public function isSecuredFor($attack) {
        if(in_array($attack, $this->getReferenceTo()->secured_for)) {
            return true;
        }
        return false;
    }
    public function getSecuredFor() {
        return $this->getReferenceTo()->secured_for;
    }
    
   
    
    /**
     * Makes a copy of this variable and adds it to the history.
     * @param Obj_Variable $var
     */
    public function toHistory() {
        $old_var = clone $this->getReferenceTo();
        // TODO: use reference?
        $this->getReferenceTo()->history[] = $old_var;
    }
    
    
    /**
     * Set the history
     * @param Obj_Variable[] $obj
     */
    public function setHistory($obj) {
        $this->history = $obj;
    }


    
    /**
     * Returns the former representations of this variable 
     * @return Obj_Variable[]
     */
    public function getHistory() {
        return $this->getReferenceTo()->history;
    }
}
