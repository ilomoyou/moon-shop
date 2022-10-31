<?php


namespace App\Http\Controllers\Wx;


use App\Http\Controllers\Controller;
use App\util\ResponseCode;

class BaseController extends Controller
{
    protected function codeReturn(array $responseCode, $data = null, $info = '')
    {
        list($errno, $errmsg) = $responseCode;
        $ret = ['errno' => $errno, 'errmsg' => $info ?: $errmsg];
        if (!is_null($data)) {
            $ret['data'] = $data;
        }
        return response()->json($ret);
    }

    protected function success($data = null)
    {
        return $this->codeReturn(ResponseCode::SUCCESS, $data);
    }

    protected function fail(array $responseCode = ResponseCode::FAIL, $info = '')
    {
        return $this->codeReturn($responseCode, null, $info);
    }
}
