<?php
/**
 * Created by PhpStorm.
 * User: walter
 * Date: 2016/8/19
 * Time: 10:05
 */
class JPushTool{
    const app_key = "9b6613473f6c2aefdcebe04d";
    const master_secret = "282d9c1defe3017ed4ff8a46";

    /**
     * 发送给所有
     * @param $content
     * @param array $ext
     * @return array|object
     */
    public static function sendToAll($content, $ext=[]){
        vendor("JPush.JPush");
        $client = new JPush(self::app_key, self::master_secret);
        $result = $client->push()
            ->setPlatform("all")
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->addAndroidNotification($content,"三联球战",1,$ext)
            ->addIosNotification($content,'iOS sound',JPush::DISABLE_BADGE,true,'iOS category',$ext)
            ->send();

        return $result;
    }

    /**
     * 定时发送给所有
     * @param $content
     * @param array $ext
     * @param int|string $time 定时
     * @return array|object
     */
    public static function sendToAllByTime($content, $time, $ext=[]){
        vendor("JPush.JPush");
        $client = new JPush(self::app_key, self::master_secret);
        $payload = $client->push()
            ->setPlatform("all")
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->addAndroidNotification($content,"三联球战",1,$ext)
            ->addIosNotification($content,'iOS sound',JPush::DISABLE_BADGE,true,'iOS category',$ext)
            ->build();
        if(is_numeric($time)){
            $timing['time'] = date("Y-m-d H:i:s",$time);
        }
        $timing['time'] = $time;
        $result = $client->schedule()->createSingleSchedule($content,$payload,$timing);
        return $result;
    }

    /**
     * 发送给某些人
     * @param $users
     * @param $content
     * @param $ext
     * @return array|object
     */
    public static function sendToSomeone($users, $content, $ext){
        vendor("JPush.JPush");
        $client = new JPush(self::app_key, self::master_secret);
        $result = $client->push()
            ->setOptions(null,null,null,true,null)
            ->setPlatform("all")
            ->addAlias($users)
            ->setNotificationAlert($content)
            ->addAndroidNotification($content,"三联球战",1,$ext)
            ->addIosNotification($content,'iOS sound',JPush::DISABLE_BADGE,true,'iOS category',$ext)
            ->send();

        return $result;
    }

    /**
     * 定时发送给某些人
     * @param $content
     * @param array $ext
     * @param int|string $time 定时
     * @return array|object
     */
    public static function sendToSomeoneByTime($users,$content, $time, $ext=[]){
        vendor("JPush.JPush");
        $client = new JPush(self::app_key, self::master_secret);
        $payload = $client->push()
            ->setPlatform("all")
            ->addAlias($users)
            ->setNotificationAlert($content)
            ->addAndroidNotification($content,"三联球战",1,$ext)
            ->addIosNotification($content,'iOS sound',JPush::DISABLE_BADGE,true,'iOS category',$ext)
            ->build();
        if(is_numeric($time)){
            $timing['time'] = date("Y-m-d H:i:s",$time);
        }else{
            $timing['time'] = $time;
        }

        $result = $client->schedule()->createSingleSchedule($content,$payload,$timing);
        return $result;
    }
}