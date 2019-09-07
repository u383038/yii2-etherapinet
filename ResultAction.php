<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 31.08.2019
 * Time: 19:52
 */

namespace backend\components;


use yii\base\Action;
use Yii;
use yii\base\InvalidConfigException;

class ResultAction extends Action
{

    public $in;
    public $track;
    public $out;

    public function run()
    {

        if(Yii::$app->request->isPost)
        {
            $data =  @json_decode(file_get_contents('php://input'), true);

            if($data['sign'] !== Yii::$app->ethApi->generateSign($data))
            {
                die('Sign wrong');
            }
            unset($data['sign']);

            switch ($data['type'])
            {
                case ('in'):
                    $this->callBack($this->in, $data);
                    break;
                case ('track'):
                    $this->callBack($this->track, $data);
                    break;
                case ('out'):
                    $this->callBack($this->out, $data);
                    break;

            }

        }
        return false;

    }

    private function callBack($callbackName, $data)
    {
        if (!is_callable($callbackName))
            throw new InvalidConfigException( get_class($this) . '::'.$callbackName.' should be a valid callback.');

        $response = call_user_func($callbackName, $data);
        return $response;
    }

}