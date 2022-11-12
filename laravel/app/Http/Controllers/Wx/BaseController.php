<?php


namespace App\Http\Controllers\Wx;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\util\ResponseCode;
use App\Verify\VerifyRequestInput;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    use VerifyRequestInput;

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

    /**
     * 成功统一返回格式
     * @param  null  $data 成功返回的数据
     * @return JsonResponse
     */
    protected function success($data = null)
    {
        return $this->codeReturn(ResponseCode::SUCCESS, $data);
    }

    /**
     * 失败统一返回格式
     * @param  array  $responseCode 响应状态码
     * @param  string  $info 错误信息
     * @return JsonResponse
     */
    protected function fail(array $responseCode = ResponseCode::FAIL, string $info = '')
    {
        return $this->codeReturn($responseCode, null, $info);
    }

    /**
     * 成功|失败统一返回格式
     * @param  bool  $isSuccess 成功|失败结果
     * @param  array  $responseCode 响应状态码
     * @param  null  $data 成功返回的数据
     * @param  string  $info 失败返回的错误信息
     * @return JsonResponse
     */
    protected function failOrSuccess(bool $isSuccess, array $responseCode = ResponseCode::FAIL, $data = null, $info = '')
    {
        if ($isSuccess) {
            return $this->success($data);
        }
        return $this->fail($responseCode, $info);
    }

    /**
     * 统一格式返回处理
     * @param  array  $responseCode 响应状态码
     * @param  null  $data 需要返回的数据
     * @param  string  $info 需要返回的提示信息
     * @return JsonResponse
     */
    protected function codeReturn(array $responseCode, $data = null, string $info = '')
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

    /**
     * 成功统一返回分页数据格式
     * @param $page
     * @return JsonResponse
     */
    protected function successPaginate($page)
    {
        return $this->success($this->paginate($page));
    }

    /**
     * 分页数据封装
     * @param $page
     * @param  array|null  $list
     * @return array
     */
    protected function paginate($page, array $list = null)
    {
        if ($page instanceof LengthAwarePaginator) {
            return [
                'total' => $page->total(),
                'page' => $page->currentPage(),
                'limit' => $page->perPage(),
                'pages' => $page->lastPage(),
                'list' => $list ?? $page->items()
            ];
        }

        if ($page instanceof Collection) {
            $page = $page->toArray();
        }

        if (!is_array($page)) {
            return $page;
        }

        $total = count($page);
        return [
            'total' => $total,
            'page' => 1,
            'limit' => $total,
            'pages' => 1,
            'list' => $page
        ];
    }

    /**
     * 鉴权后获取用户信息
     * @return User|null
     */
    protected function user()
    {
        return Auth::guard('wx')->user();
    }

    /**
     * 判断是否登录
     * @return bool
     */
    protected function isLogin()
    {
        return !is_null($this->user());
    }

    /**
     * 获取用户ID
     * @return mixed
     */
    protected function userId()
    {
        return $this->user()->getAuthIdentifier();
    }
}
