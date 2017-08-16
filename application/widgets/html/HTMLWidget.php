<?php

/**
 * Represents a generic HTML element
 */
class HTMLWidget extends Widget
{
	// The name of the array present in the data array given to the view that will render this widget
    const HTML_ARG_NAME = 'HTML';
	const HTML_DEFAULT_VALUE = ''; // Default value of the html element
    const HTML_NAME = 'name'; // HTML name attribute
    const HTML_ID = 'id'; // HTML id attribute
    
    // 
    const EXTERNAL_BLOCK = 'externalBlock'; // 
    const EXTERNAL_START_BLOCK_HTML_TAG = '<div>'; //
    const EXTERNAL_END_BLOCK_HTML_TAG = '</div>'; // 
    
    // 
    const LABEL = 'title';
	const REGEX = 'regex';
	const TITLE = 'description';
	const REQUIRED = 'udf-required';
	const MAX_VALUE = 'max-value';
	const MIN_VALUE = 'min-value';
	const MAX_LENGTH = 'max-length';
	const MIN_LENGTH = 'min-length';
	const PLACEHOLDER = 'placeholder';
    
    /**
     * It gets also the htmlArgs array as parameter, it will be used to set the HTML properties
     */
    public function __construct($name, $args = array(), $htmlArgs = array())
	{
		parent::__construct($name, $args);
		
		// Initialising HTML properties
		$this->_setHtmlProperties($htmlArgs);
        
        // Loads helper message to manage returning messages
		$this->load->helper('message');
	}
    
    /**
     * Initialising html properties, such as the id and name attributes of the HTML element
     */
    private function _setHtmlProperties($htmlArgs)
    {
		// If $htmlArgs wasn't already stored in $this->_args
		if (!isset($this->_args[HTMLWidget::HTML_ARG_NAME]))
		{
			$this->_args[HTMLWidget::HTML_ARG_NAME] = array();
			
			// Avoids that elements in a HTML page have the same name or id
			$randomIdentifier = uniqid(rand(0, 1000));
			$this->_args[HTMLWidget::HTML_ARG_NAME][HTMLWidget::HTML_ID] = $randomIdentifier;
			$this->_args[HTMLWidget::HTML_ARG_NAME][HTMLWidget::HTML_NAME] = $randomIdentifier;
			
			foreach($htmlArgs as $argName => $argValue)
			{
				$this->_args[HTMLWidget::HTML_ARG_NAME][$argName] = $argValue;
			}
		}
    }
    
    /**
	 * 
	 */
	public static function printAttribute($htmlArgs, $attribute, $isValuePresent = true)
	{
		if ($attribute != null)
		{
			if (isset($htmlArgs[$attribute]))
			{
				if ($isValuePresent === true)
				{
					$value = $htmlArgs[$attribute];
					
					if (is_bool($value))
					{
						$value = $value ? 'true' : 'false';
					}
					
					echo sprintf('%s="%s"', $attribute, $value);
				}
				else
				{
					echo $attribute;
				}
			}
		}
	}
	
	/**
	 * 
	 */
	public static function printStartBlock($htmlArgs)
	{
		if (isset($htmlArgs[HTMLWidget::EXTERNAL_BLOCK])
			&& $htmlArgs[HTMLWidget::EXTERNAL_BLOCK] === true)
		{
			echo HTMLWidget::EXTERNAL_START_BLOCK_HTML_TAG;
		}
	}
	
	/**
	 * 
	 */
	public static function printEndBlock($htmlArgs)
	{
		if (isset($htmlArgs[HTMLWidget::EXTERNAL_BLOCK])
			&& $htmlArgs[HTMLWidget::EXTERNAL_BLOCK] === true)
		{
			echo HTMLWidget::EXTERNAL_END_BLOCK_HTML_TAG;
		}
	}
}