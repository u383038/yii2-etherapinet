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
        if(!$this->apiSecretKey === null)
        {
            throw new InvalidConfigException("Need set apiSecretKey");
        }
    }


    /**
     * @param string|null $uniqID
     * @param string|null $token
     * @param string|null $address
     * @param string|null $tag
     * @param string|null $statusURL
     * @return bool|string
     * @throws HttpException
     */
    public function createWallet(string $uniqID = null, string $token = null, string $address = null,
                                 string $tag = null, string $statusURL = null)
    {
        $response = $this->createRequest($data = [
            'method' => 'give',
            'tag' => $tag,
            'token' => $token,
            'statusURL' => $statusURL,
            'uniqID' => $uniqID,
            'address' => $address

        ]);


        if($response['result'])
        {
            return $response['result'];
        }

        if(YII_DEBUG)
        {
            throw new HttpException("wallet did not created");
        }
        return false;


    }

    /**
     * Get the total balance of all your wallets
     *
     * @return string
     */
    public function getBalance() : string
    {

        $response = $this->createRequest($data = [
            'method' => 'balance'
        ]);

        return $response['result'];
    }


    /**
     * @param string $address
     * @param float $amount
     * @param string $tag
     * @param string $token
     * @param string $statusURL
     * @param string $uniqID
     */
    public function setTrack(string $address, float $amount, string $tag,
                             string $token = '',  string $uniqID = '', string $statusURL = '')
    {

        $this->createRequest($data = [
            'method' => 'track',
            'address' => $address,
            'amount' => $amount,
            'tag' => $tag,
            'token' => $token,
            'statusURL' => $statusURL,
            'uniqID' => $uniqID

        ]);
    }

    /**
     * @param string $address
     * @param float $amount
     * @param float $price
     * @param int $limit
     * @param string $tag
     * @param string $uniqID
     * @param string $token
     * @param string $statusURL
     * @param int $returnTransaction
     * @return int
     */
    public function send(string $address, float $amount, float $price, int $limit, string $tag,
                         string $uniqID = '', string $token, string $statusURL, int $returnTransaction) : int
    {
        $response = $this->createRequest($data = [
            'method' => 'track',
            'address' => $address,
            'amount' => $amount,
            'price' => $price,
            'limit' => $limit,
            'returnTransaction' => $returnTransaction,
            'tag' => $tag,
            'token' => $token,
            'statusURL' => $statusURL,
            'uniqID' => $uniqID
        ]);

        return $response['result'];

    }

    /**
     * @param array $data
     * @return array
     */
    private function createRequest(array $data) : array
    {

        $data['key'] = $this->apiSecretKey;

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