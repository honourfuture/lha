<?php
namespace index\Controller;
use Think\Controller;
class WheelController extends Controller {
    
    public function index(){
        if (!isLogin()) {
            msg('请登录后再进行抽奖！', 2, U('mobile/login'));
        }
        
        $uid=$_SESSION['uid'];
        
        $result=M('wheel')->where(array('id'=>1))->find();
        /*$gl=array(
            '0'=>$result['odds1'],
            '1'=>$result['odds2'],
            '2'=>$result['odds3'],
            '3'=>$result['odds4'],
            '4'=>$result['odds5'],
            '5'=>"100%"
        );
        $gl=json_encode($gl);
        
        $name=array(
            '0'=>$result['name1'],
            '1'=>$result['name2'],
            '2'=>$result['name3'],
            '3'=>$result['name4'],
            '4'=>$result['name5'],
            '5'=>"谢谢参与",
        );
        $name=json_encode($name,JSON_UNESCAPED_UNICODE);
        $this->assign('gl',$gl);
        $this->assign('name',$name);*/
        $this->assign('uid',$uid);
        $this->assign('result',$result);
        $this->display();
        
    }
    
    
    public function start(){
        
        if (!isLogin()) {
            msg('请登录后再进行抽奖！', 2, U('mobile/login'));
        }
        $uid=$_SESSION['uid'];
        
        $user = getData('user', 1, 'id = \'' . $uid . '\'');
        $item=I('item');
        $result=M('wheel')->where(array('id'=>1))->find();
        if($user['wheel_num']>0){
            
            M('user')->where(array('id'=>$uid))->setDec("wheel_num",1);
            if($item==3 || $item==4 || $item==5){
                M('user')->where(array('id'=>$uid))->setInc("money",$result['money'.$item]);
            }
            $data['uid']=$uid;
            $data['phone']=$user['phone'];
            $data['type']=$item;
            $data['ctime']=date("Y-m-d");
            if($item==6){
                $data['title']="谢谢参与";
            }else{
                $data['title']=$result['name'.$item];
                
            }
            M('wheel_log')->add($data);
            $arr['code']=1;
            if($item==6){
                $arr['msg']="请继续投资，在接在历！";
            }else{
                $arr['msg']=$result['reason'.$item];
            }
            
            
            $this->ajaxreturn($arr);
            
        }else{
            
            $arr['code']=0;
            $arr['msg']="抽奖次数不足，请投资后再进行抽奖！";
            $this->ajaxreturn($arr);
        }
        
    }
}