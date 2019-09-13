Yii2 Etherapi.net
===============
Extension for integration Ethereum(Etherapi.net) in yii2 project. 

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist u383038/yii2-etherapi "*"
```

or add

```
"u383038/yii2-etherapi": "*"
```

to the require section of your `composer.json` file.

Update config file config/main.php
```php
return [
    'components' => [
        'ETH' => [
            'class' => 'backend\components\Component',
            'apiSecretKey' => '',
        ],
    ],
]
```


Usage
-----
Example of creating user wallets, withdraw:
```php
class EthController extends Controller
{
    public function actions()
    {
        return [
            'result' => [
                'class' => 'backend\components\ResultAction',
                'in' => [$this, 'inCallback'],
                'track' => [$this, 'trackCallback'],
                'out' => [$this, 'outCallback'],
            ]
        ];
    }
    
    public function actionCreateWallet()
    {
        if(Yii::$app->user->identity->ethwallet === null)
        {
            $user = User::findOne(Yii::$app->user->getId());
            $params = [
                'tag' => $user->id,
                'token' => '',
                'address' => '',
                'uniqID' => '',
                'statusURL' => ''
            ];
            if(($address = Yii::$app->ETH->createWallet($params)) && $user->updateAttributes(['ethwallet' => $address]))
            {
                return $this->render('account', ['address' => $address]);
            }
        }
        return $this->render('account');
    }
    
    public function actionWithdraw()
    {
        $model = new Withdraw();
        $user = User::findOne(Yii::$app->user->getId());

        if($model->load(Yii::$app->request->post()))
        {
            if ($model->amount >= Yii::$app->ETH->getBalance())
                return false;

            $transaction = Yii::$app->db->beginTransaction();
            if($model->save() && $user->updateAttributes(['balance' => $user->balance - $model->amount]))
                $transaction->commit();
            else
                $transaction->rollBack();

            $params = [
                'address' => $model->address,
                'amount' => $model->amount,
                'price' => $model->price,
                'limit' => $model->limit,
                'tag' => $model->id,
                'uniqID' => '',
                'token' => '',
                'returnTransaction' => '',
                'statusURL' => ''
            ];
            if(Yii::$app->ETH->send($params))
            {
                return $this->redirect('site/transactions');
            }
        }
        return $this->render('withdraw', ['model' => $model]);
        
    public function inCallback($data)
    {
        switch ($data['confirmations'])
        {
            case (1):
                //do something after 1 confirmation, ex. update status in transactions
                break;
            case (12):
                //do something after 12 confirmation, payment has passed
                $invoice = new Invoice();
                $invoice->amount = $data['amount'];
                $invoice->user_id = $data['tag'];
                $invoice->datetime = date('Y-m-d h:i:s', $data['date']);
                $invoice->status = Invoice::STATUS_SUCCESS;
                $user = User::findOne($data['tag']);

                $newBalance = doubleval($user->balance) + doubleval($data['amount']);

                $transaction = Yii::$app->db->beginTransaction();
                if($invoice->save() && $user->updateAttributes(['balance' => $newBalance]))
                    $transaction->commit();
                else
                {
                    $transaction->rollBack();
                    $invoice->updateAttributes(['status' => Invoice::STATUS_FAIL]);
                }
                break;
        }
    }

    public function trackCallback($data)
    {

        switch ($data['confirmations'])
        {
            case (1):
                //do something after 1 confirmation, ex. update status in transactions
                break;
            case (12):
                //do something after 12 confirmation, payment has passed
        }

    }

    public function outCallback($data)
    {
        switch ($data['confirmations'])
        {
            case (1):
                //do something after 1 confirmation, ex. update status in transactions
                $withdraw = Withdraw::findOne($data['tag']);
                $withdraw->updateAttributes(['status' => Withdraw::STATUS_PENDING]);
                break;
            case (12):
                //do something after 12 confirmation, payment has passed
                $withdraw = Withdraw::findOne($data['tag']);
                $withdraw->updateAttributes(['status' => Withdraw::STATUS_SUCCESS]);
                break;
        }

    }    
```
Warning. This service is non-official of third-party developers. 