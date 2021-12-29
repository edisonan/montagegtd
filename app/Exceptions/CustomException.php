<?php
namespace App\Exceptions;

use App\Http\Utils\ResponseDataUtil;

class CustomException extends \Exception
{

    private $result = [];

    /**
     * BusinessException constructor.
     *
     * @param string $message
     * @param string $code
     * @param array $result
     */
    public function __construct(string $message, string $code, $result = [])
    {
        $this->code = $code  ? : ResponseDataUtil::COMMON_ERROR;
        $this->message  = $message ? : ResponseDataUtil::getMessage($this->code);
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * 异常输出
     */
    public function render($request)
    {
    	if ($request->ajax () || $request->wantsJson ()) {
    		return response()->json([
	            'result' => $this->getData(),
	            'code' => $this->getCode(),
	            'msg' => $this->getMessage(),
	        ], 200);
    	} else {
    		return response()->view(
                'errors.custom',
                array(
                    'exception' => $this
                )
        	);
    	}
        
    }
}