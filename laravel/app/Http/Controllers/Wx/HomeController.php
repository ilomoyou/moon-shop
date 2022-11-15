<?php


namespace App\Http\Controllers\Wx;


use App\Exceptions\ParametersException;
use Illuminate\Http\RedirectResponse;

class HomeController extends BaseController
{
    protected $only = [];

    /**
     * 分享链接重定向跳转
     * @return RedirectResponse
     * @throws ParametersException
     */
    public function redirectShareUrl()
    {
        $id = $this->verifyId('id');
        $type = $this->verifyString('type', 'groupon');

        if ($type == 'groupon') {
            return redirect()->to(env('H5_URL') . "/#/items/detail/$id");
        }
        return redirect('/not-found');
    }
}
