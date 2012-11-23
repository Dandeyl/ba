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
     * By what mechanics it is resolved
     * @var array 
     */
    protected $secured_by = array();
    
    /**
     * Did an error occur resolving the function? E.g. a function that is not executable
     * had to be executed to get its value.
     * @var bool 
     */
    protected $resolve_error = false;
    
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
    
    /**
     * Is this expression userdefined
     * @param type $userdefined
     */
    public function setUserDefined($userdefined) {
        $this->user_defined = (int) $userdefined;
    }
    public function isUserDefined() {
        return $this->user_defined;
    }
    
    
    
    
    
    
    /**
     * Set the mechanisms this expression is secured by
     * @return array
     */
    public function setSecuredBy($secured_by) {
        if(is_array($secured_by)) {
            $this->secured_by = $secured_by;
        }
        elseif(is_string($secured_by)) {
            $this->secured_by = array($secured_by);
        }
        else {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei setSecuredFor: ".var_export($secured_by, true));
        }
    }
    
    /**
     * Adds an mechanism this expression is secured by
     * @param string $mechanism
     */
    public function addSecuredBy($mechanism) {
        if(!is_string($mechanism)) {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei addSecuredFor: ".var_export($mechanism, true));
        }
        
        if($mechanism == Securing::NotUserDefined) {
            $this->setUserDefined(false);
        }
        else {
            $this->secured_by[] = $mechanism;
        }
    }
    
    /**
     * Removes an mechanism this expression is secured by
     * @param string $mechanism
     */
    public function removeSecuredBy($mechanism) {
        if(!is_string($mechanism)) {
            throw new Exception("Obj_Resolved: Ungültiger Wert bei addSecuredFor: ".var_export($mechanism, true));
        }
        
        $key = array_search($mechanism, $this->secured_by);
        if($key !== false) {
            unset($this->secured_by[$key]);
        }
    }
    
    /**
     * Get the mechanisms this expression is secured by
     * @return array
     */
    public function getSecuredBy() {
        return (array) $this->secured_by;
    }
    
    /**
     * Returns if the the resolved expression was secured by a specific mechanism.
     * @param string $mechanism
     * @return bool
     */
    public function isSecuredBy($mechanism) {
        return in_array($mechanism, $this->secured_by);
    }
    
    /**
     * Set if an error occured resolving this value
     * @param type $error_occured
     */
    public function setResolveError($error_occured) {
        $this->resolve_error = (bool) $error_occured;
    }
    
    /**
     * Did an error occur resolving this function?
     * @return bool
     */
    public function isResolveError() {
        return $this->resolve_error;
    }
}