<?php
namespace app\models;

use himiklab\yii2\recaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\Model;
use himiklab\yii2\recaptcha\ReCaptchaValidator2;
use yii\httpclient\Client;

class RegisterForm extends Model
{
    public $username;
    public $password;
    public $reCaptcha;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'validateUsername'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['password', 'string', 'min' => 6],
        ];
    }

    public function register()
    {
        if (!$this->validate()) {
            return null;
        }

        if (!$this->validateRecaptcha($this->reCaptcha)) {
            $this->addError('reCaptcha', 'Подтверждение reCAPTCHA не прошло.');
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        $scenario = new Scenario();
        $scenario->name = "Тестовый сценарий";
        $scenario->model_id = 1;

        return ($user->save() && $scenario->save()) ? $user : null;
    }

    private function validateRecaptcha($token)
    {
        $secret = '6LcjPgYqAAAAAOy0rdIzCW1c2KkLkFTU_kMt4x4k'; // Ваш секретный ключ reCAPTCHA
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token");
        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'];
    }
    private function validateUsername($username)
    {
        return !(User::find()->where(['username' => $username])->exists());
    }
}
