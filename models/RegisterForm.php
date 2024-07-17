<?php
namespace app\models;

use Exception;
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
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['password', 'string', 'min' => 6],
            ['username', 'validateUsername'],
        ];
    }

    public function register()
    {
        if (!$this->validate()) {
            return $this->createErrorResponse($this->getErrors());
        }

        if (!$this->validateRecaptcha($this->reCaptcha)) {
            $this->addError('reCaptcha', 'Подтверждение reCAPTCHA не прошло.');
            return $this->createErrorResponse($this->getErrors());
        }

        $user = new User();
        $user->username = $this->username;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        if (!$user->save()) {
            $this->addErrors($user->getErrors());
            return $this->createErrorResponse($this->getErrors());
        }

        $scenario = new Scenario();
        $scenario->name = "Тестовый сценарий";
        $scenario->model_id = 1;
        $scenario->user_id = $user->id;

        if ($scenario->save()) {
            $auth = Yii::$app->authManager;
            $auth->assign($auth->getRole('user'), $user->id);
            return $this->createSuccessResponse($user);
        } else {
            $this->addErrors($scenario->getErrors());
            return $this->createErrorResponse($this->getErrors());
        }
    }

    private function validateRecaptcha($token)
    {
        $secret = '6LcjPgYqAAAAAOy0rdIzCW1c2KkLkFTU_kMt4x4k'; // Ваш секретный ключ reCAPTCHA
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$token");
        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'];
    }
    public function validateUsername($attribute, $params)
    {
        if (User::find()->where(['username' => $this->username])->exists()) {
            $this->addError($attribute, 'Имя пользователя уже занято.');
            return false;
        }
        return true;
    }
    private function createErrorResponse($errors)
    {
        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'errors' => $errors,
        ];
    }

    private function createSuccessResponse($user)
    {
        return [
            'status' => 'success',
            'user' => $user,
        ];
    }
}
