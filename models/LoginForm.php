<?php

namespace app\models;

use himiklab\yii2\recaptcha\ReCaptchaValidator2;
use himiklab\yii2\recaptcha\ReCaptchaValidator3;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $reCaptcha;

    private $_user;
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    public function login()
    {
        if (!$this->validateRecaptcha($this->reCaptcha)) {
            $this->addError('reCaptcha', 'Подтверждение reCAPTCHA не прошло.');
            return null;
        }
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), 3600*24*30);
        }
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }

    private function validateRecaptcha($token)
    {
        $secret = '6LcjPgYqAAAAAOy0rdIzCW1c2KkLkFTU_kMt4x4k'; // Ваш секретный ключ reCAPTCHA
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token");
        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'];
    }
}