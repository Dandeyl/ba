<?php
if(!class_exists('Obj_VulnerabilityList', false)) {
    require(dirname(__FILE__).'/../../scanner/objects/vulnerabilitylist.php');
}
if(!class_exists('Obj_Vulnerability', false)) {
    require(dirname(__FILE__).'/../../scanner/objects/vulnerability.php');
}

class ScanResult implements Iterator {
    /**
     *
     * @var type Total number of vuln.
     */
    protected $num_vulnerabilities;
    
    /**
     *
     * @var type Number of vuln. for each attack
     */
    protected $num_vulnerabilities_detailed;
    
    
    /**
     *
     * @var type List of vulnerabilities
     */
    protected $vulnerabilities;
    
    protected $current_vuln = null;
    
    /**
     * Constructor
     * @param string $scaninfo_file
     * @throws Exception
     */
    public function __construct($scaninfo_file) {
        if(!file_exists($scaninfo_file)) {
            throw new Exception("Class ScanResult: Given file could not be found!");
        }
        
        $scaninfo = unserialize(file_get_contents($scaninfo_file));
        
        
        $vulnlist = $scaninfo['vuln'];
        /* @var $vulnlist Obj_VulnerabilityList */
        $this->num_vulnerabilities = $vulnlist->getNumVulnerabilities();
        $this->vulnerabilities = array();
        
        $hlp = $vulnlist->getVulnerabilities();
        
        foreach($hlp as $vuln) {
            /* @var $vuln Obj_Vulnerability */
            @$this->num_vulnerabilities_detailed[$vuln->getType()]++;
            $this->vulnerabilities[] = $vuln;
        }
    }
    
    
    public function getNumVulnerabilities($type=null) {
        if($type === null) {
            return $this->num_vulnerabilities;
        }
        
        else {
            if(isset($this->num_vulnerabilities_detailed[$type])) {
                return $this->num_vulnerabilities_detailed[$type];
            }
            return 0;
        }
    }
    
    
    
    
    public function getExpression() {
        return $this->current_vuln->getNode();
    }
    
    
    
    
    
    /////////////////////////////////////////
    ////    Iterator methods            /////
    /////////////////////////////////////////
    public function current() {
        return $this->current_vuln = current($this->vulnerabilities);
    }

    public function key() {
        return key($this->vulnerabilities);
    }

    public function next() {
        return $this->current_vuln = next($this->vulnerabilities);
    }

    public function rewind() {
        return $this->current_vuln = reset($this->vulnerabilities);
    }

    public function valid() {
        return (bool) $this->current_vuln;
    }
}