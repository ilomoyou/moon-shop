<?php


namespace App\Http\Controllers\Wx;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\util\ResponseCode;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected $only;
    protected $except;

    public function __construct()
    {
        // 统一鉴权认证
        $option = [];
        if (!is_null($this->only)) {
            $option['only'] = $this->only;
        }
        if (!is_null($this->except)) {
            $option['except'] = $this->except;
        }
        $this->middleware('auth:wx', $option);
    }

    protected function codeReturn(array $responseCode, $data = null, $info = '')
    {
        list($errno, $errmsg) = $responseCode;
        $ret = ['errno' => $errno, 'errmsg' => $info ?: $errmsg];
        if (!is_null($data)) {
            if (is_array($data)) {
                $data = array_filter($data, function ($item) {
                    return $item !== null;
                });
            }
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

    protected function failOrSuccess($isSuccess, array $responseCode = ResponseCode::FAIL, $data = null, $info = '')
    {
        if ($isSuccess) {
            return $this->success($data);
        }
        return $this->fail($responseCode, $info);
    }

    /**
     * @return User|null
     */
    protected function user()
    {
        return Auth::guard('wx')->user();
    }
}
