<?php

namespace App\Admin\Controllers;

use App\Admin\Api\Qweixin;
use App\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\JsonResponse;
//use Dcat\Admin\Models\Administrator;
use App\Models\AdminUser as Administrator;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use http\Client\Response;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\SessionGuard;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ApiController extends AdminController
{
    public function users(Request $request)
    {
        $Qweixin = new Qweixin();
        $userInfo = $Qweixin->getUserInfo('wanghaichao');
        $to = 'wanghaichao@3k.com';
        $password = Str::random(10);
        $title = 'Tapplus业务中心密码重置通知！';
        $content = '您的Tapplus业务中心密码已重置，请登录后修改密码！'."\r\n".'密码：'.$password;
        //Auth::guard('admin')->login($user);
        //return redirect('/admin');
        //return User::where('name', 'like', "%$q%")->paginate(null, ['id', 'name as text']);
        Mail::raw($content, function ($message) use ($title,$to){
            $message->to($to)->subject($title)->from(env('MAIL_FROM_ADDRESS'),'业务中心密码重置');
        });
        dd(Mail::failures());
        //return $Qweixin->setmessage('fuqiulong|zhaosiye','Tapplus业务中心的密码有重置：密码：'.$password);
    }

    public function thirdLogin(Request $request)
    {
        $Qweixin = new Qweixin();
        $userId = $Qweixin->getUserId($_GET['code']);
        $password = Str::random(10);
        if ($userId && isset($_GET['state']) && $_GET['state'] == 'qwx_login') {
            $userInfo = $Qweixin->getUserInfo($userId);
            $User = new Administrator();
            /** @var \Illuminate\Database\Eloquent\Factory $factory */
            $data = $User->where('username',$userId)->pluck('id')->toArray();
            if(empty($data)){
                $model = $User->updateOrCreate(
                    ['username' => $userId],
                    [
                        'name' => $userInfo['name'],
                        'email' => $userInfo['email'],
                        'email_verified_at' => now(),
                        'password' =>bcrypt($password),
                        'remember_token' => Str::random(10),
                        'is_first' =>1,
                    ]
                );
                $id = $model->id;
            }else{
                $id =$data[0];
            }

            $user = Administrator::find($id);
            Auth::guard('admin')->login($user);
            $request->session()->put('is_first',1);
            return redirect('/admin');
        }


    }
}
