<?php


namespace u383038\eth;

use yii\httpclient\Client;
use yii\base\InvalidConfigException;
use u383038\eth\exceptions\HttpException;

class Component extends \yii\base\Component
{

    public $apiSecretKey;

    const URL = 'https://etherapi.net/api/v2';

    public function init()
    {
        parent::init();
        if($this->apiSecretKey === null)
        {
            throw new InvalidConfigException("Need set apiSecretKey");
        }
    }

    /**
     * Create a wallet for accept payments
     * @param array $params
     * @return bool|mixed
     * @throws HttpException
     */
    public function createWallet(array $params)
    {
        $response = $this->createRequest($params, $method = 'give');

        if($response['result']) return $response['result'];

        if(YII_DEBUG) throw new HttpException("wallet did not created");

        return false;
    }

    /**
     * Get the total balance of all your wallets
     * @return string
     */
    public function getBalance() : string
    {
        $response = $this->createRequest($params = [], $method = 'balance');
        return $response['result'];
    }

    /**
     * Expect a certain payment
     * @param array $params
     * @return array
     */
    public function setTrack(array $params)
    {
        $response = $this->createRequest($params, $method = 'track');
        return $response;
    }


    /**
     * @param array $params
     * @return int
     */
    public function send(array $params) : int
    {
        $response = $this->createRequest($params, $method = 'send');
        return $response['result'];
    }

    /**
     * @param array $data
     * @param string $method
     * @return array
     */
    private function createRequest(array $data, string $method) : array
    {
        $data = array_diff($data, array(''));
        $data['key'] = $this->apiSecretKey;
        $data['method'] = $method;

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl(self::URL)
            ->setData($data)
            ->send();

        return $response->data;
    }

    /**
     * @param array $data
     * @return string
     */
    public function generateSign(array $data) : string
    {
        unset($data['etherapi.net']);
        unset($data['sign']);
        $data[] = $this->apiSecretKey;

        $sign = sha1(
            implode(':', $data)
        );

        return $sign;
    }

}