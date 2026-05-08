<?php

class CException extends RuntimeException 
{
	/**
     * 用于保存错误级别
     * @var integer
     */
    protected $severity;

    /**
     * 错误异常构造函数
     * @param integer $severity 错误级别
     * @param string  $message  错误详细信息
     * @param string  $file     出错文件路径
     * @param integer $line     出错行号
     * @param array   $context  错误上下文，会包含错误触发处作用域内所有变量的数组
     */
	public function __construct($severity, $message, $file, $line, array $context = array())
    {
        $this->severity = $severity;
        $this->message  = $message;
        $this->file     = $file;
        $this->line     = $line;
        $this->code     = 0;
		
        empty($context) || $this->setData('Error Context', $context);
    }
	
	public function CException($severity, $message, $file, $line, $context)
	{
		$this->__construct($severity, $message, $file, $line, $context);
	}
	
	protected function parseClass($className)
	{
		return $className;
	}
	
	protected function parseArgs($args)
	{
		if (is_array($args))
			$args = json_encode($args);
		return $args;
	}
	
	protected function parseFileInfo($file, $line)
	{
		return $file."($line)";
	}
	
	public function errorMessage()
	{
		//error message
		$errorMsg = "<li>Error on line ".$this->getLine().' in '.$this->getFile()
			.': '.$this->getMessage().': ErrorCode='.$this->getCode()."</li>\n";

		$errorMsg .="<li>CallTrace:\n".$this->getTraceAsString()."</li>\n";
		
		//Exception
		$tracedb = $this->getTrace();
				
		// Show Function
		foreach ($tracedb as $key=>$v) { 
		
			if ($v['function']){
				$errorMsg .= "<li>";
				
				$errorMsg .= sprintf(
						"at %s%s%s(%s) in %s", 
						isset($v['class']) ? $this->parseClass($v['class']) : '',
						isset($v['type'])  ? $v['type'] : '', 
						$v['function'], 
						isset($v['args'])?$this->parseArgs($v['args']):'',
						isset($v['file'])?$this->parseFileInfo($v['file'], $v['line']):''
						);
						
				$errorMsg .= "</li>\n";
			}
		}
				
		return $errorMsg;
		
		//throw $this;
	}
}
?>