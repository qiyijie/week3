<?php

namespace app\Article\controller;

use app\Article\model\Article;
use think\Controller;

class Index extends Controller
{
    public function show(){
       $data = Article::select()->toArray();
       return $data;
    }
    public function update(){
        $id = input('id');
        $res = model('Article')->find($id)->toArray();
        $req = Article::update(['collect'=>$res['collect']+1,'show'=>1],['id'=>$id]);
        if ($req){
            return $res['collect']+1;

        }
    }
}
