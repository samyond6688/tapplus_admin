<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cate extends Model
{
    protected $fillable = [
       'id', 'name', 'developer', 'sign_id','cooperation_mode','cate_theme_id','cate_type_id','game_secret','app_sign','status','mark'
    ];
    protected $initId = 1001;

    public static $developerConfig = [
        1 =>'顶工作室1',
        2 =>'顶工作室2',
        3 =>'顶工作室3',
    ];

    public static $signConfig = [
        1 =>'上海游民1',
        2 =>'上海游民2',
        3 =>'上海游民3',
    ];

    public static $cooperationModeConfig = [
        1 =>'独代',
        2 =>'定制',
        3 =>'联运',
        4 =>'自研',
    ];

    public function games(){
        return $this->hasMany(Game::class,'cate_id');
    }

    public function getId(){
        $maxId = DB::table('cates')->max('id');
        return $maxId ? $maxId + 1 : $this->initId;
    }
}
