<?php

namespace DialInno\Jaal\Core\Api;

class Version
{

	public function __construct(string $version)
	{
        //construct a version number remove any chars that are not numbers or a .
		$this->version =  preg_replace("/[^0-9\.]/","", (string)$version);

	}

	/**
     * Get a more semantical version number if
     * if needed for use in class
     *
     * @return string 
     * 
     **/
	public function getSemanticVersion()
    {
        return str_replace('_', ".",$this->version);
    }
    /**
     * If an api version is given remove . since it is illegal character 
     * for class names.
     *
     * @return string
     * 
     **/
    public function getFormattedForClassNameVersion(){
        //since classes cant have . in class names, we will replace all . with _
        return str_replace(".","_",$this->version);
    }
}