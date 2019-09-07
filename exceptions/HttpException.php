<?php

namespace u383038\eth\exceptions;

use yii\base\Exception;

class HttpException extends Exception
{
    public function getName()
    {
        return 'Invalid http response';
    }
}