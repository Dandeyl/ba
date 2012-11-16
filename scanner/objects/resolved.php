<?php
/**
 * Represents a resolved piece of code, e.g. for getting the content of a variable.
 */
class Obj_Resolved extends Obj_CodeSequenceInfo {
    /**
     * Resolved value. If it's not possible to safely resolve the value it is null.
     * @var mixed 
     */
    protected $value;
    
    /**
     * Is the resolved value user defined?
     * @var bool 
     */
    protected $user_defined = false;
    
    /**
     * For which attacks this
     * @var type 
     */
    protected $secured_for = array();
    
    /**
     * Sets the resolved value.
     * @param mixed $value
     */
    public function setValue($value) {
        $this->value = $value;
    }
    
    /**
     * Gets the resolved value
     * @return type
     */
    public function getValue() {
        return $this->value;
    }
    
    
    public function setUserDefined($userdefined) {
        $this->user_defined = (bool) $userdefined;
    }
    public function isUserDefined() {
        return (bool) $this->user_defined;
    }
    
    
    
    
    
    
    /**
     * Set the attack types this expression is secured for
     * @return array
     */
    public function setSecuredFor($secured_for) {
        if(is_array($secured_for)) {
            $this->secured_for = $secured_for;
        }
        elseif(is_string($secured_for)) {
            $this->secured_for = array($secured_for);
        }
        else {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei setSecuredFor: ".var_export($secured_for, true));
        }
    }
    
    /**
     * Adds an attack type this expression is secured for
     * @param string $secured_for
     */
    public function addSecuredFor($secured_for) {
        if(!is_string($secured_for)) {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei addSecuredFor: ".var_export($secured_for, true));
        }
        
        if(!in_array($secured_for, $this->secured_for)) {
            $this->secured_for[] = $secured_for;
        }
    }
    
    
    /**
     * Removes an attack type this expression is secured for
     * @param string $secured_for
     */
    public function removeSecuredFor($secured_for) {
        if(!is_string($secured_for)) {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei addSecuredFor: ".var_export($secured_for, true));
        }
        
        $key = array_search($secured_for, $this->secured_for);
        if($key !== false) {
            unset($this->secured_for[$key]);
        }
    }
    
    /**
     * Get the attack types this expression is secured for
     * @return array
     */
    public function getSecuredFor() {
        return (array) $this->secured_for;
    }
    
    /**
     * Returns if the the resolved expression was secured for a specific attack.
     * @param string $attack
     * @return bool
     */
    public function isSecuredFor($attack) {
        return in_array($attack, $this->secured_for);
    }
}