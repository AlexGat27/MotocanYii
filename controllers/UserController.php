<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Controller;

class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Убедитесь, что поведение ContentNegotiator установлено для правильного формата ответа
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [                   // restrict access to domains:
                'Origin' => [
                    'http://localhost:8080', 'http://localhost:5173'
                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 3600 * 5,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'except' => ['login', 'register', 'check-auth', 'check-admin'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['logout'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => false,
                    'roles' => ['banned'],
                    'denyCallback' => function ($rule, $action) {
                        Yii::$app->response->statusCode = 403;
                        return ['status' => 'error', 'message' => 'You are banned.'];
                    },
                ],
                [
                    'allow' => true,
                    'roles' => ['users'],
                ],
            ],
        ];
        return $behaviors;
    }
    public function actionRegister()
    {
        $model = new RegisterForm();
        $request = Yii::$app->request->getBodyParams();
        $model->username = $request["username"];
        $model->password = $request["password"];
        $model->reCaptcha = $request["reCaptcha"];
        return $model->register();
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->username = Yii::$app->request->post('username');
        $model->password = Yii::$app->request->post('password');
        $model->reCaptcha = Yii::$app->request->post('reCaptcha');
        if ($model->login()) {
            return [
                'success' => true,
                'message' => 'Login successful.',
                'user' => Yii::$app->user->identity,
            ];
        }

        return [
            Yii::$app->response->statusCode = 401,
            'success' => false,
            'errors' => $model->errors,
        ];
    }

    public function actionLogout(){
        Yii::$app->user->logout();
    }

    public function actionCheckAuth()
    {
        if (Yii::$app->user->isGuest) {
            return ['status' => 'unauthorized'];
        }
        $auth = Yii::$app->authManager;
        $user = Yii::$app->user->identity;
        $rolesUser = $auth->getRolesByUser(Yii::$app->user->id);
        return [
            'status' => 'authorized',
            'id' => $user->id,
            'username' => $user->username,
            'created_at' => $user->created_at,
            'roles' => array_keys($rolesUser),
        ];
    }

    /**
     * Action для получения списка пользователей.
     *
     * @return array
     */
    public function actionIndex()
    {
        $users = User::find()
            ->where(['!=', 'id', '1'])
            ->andWhere(['!=', 'id', Yii::$app->user->identity->id])
            ->all();
        $auth = Yii::$app->authManager;
        $userList = [];

        foreach ($users as $user) {
            $roles = $auth->getRolesByUser($user->id);
            $userList[] = $this->generateSuccessResponse($user, $roles);
        }

        return [
            'success' => true,
            'users' => $userList,
        ];
    }

    /**
     * Action для удаления пользователя.
     *
     * @param int $id ID пользователя для удаления
     * @return array
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Пользователь не найден.',
            ];
        }
        $auth = Yii::$app->authManager;
        if ($user->delete() && $auth->revokeAll($user->id)) {
            return [
                'success' => true,
                'message' => 'Пользователь успешно удален.',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при удалении пользователя.',
            ];
        }
    }
    public function actionAssignRole($id)
    {
        $request = Yii::$app->request->post();
        $roleName = $request['roleName'];

        $user = $this->findModel($id);
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);

        if (!$role) {
            Yii::$app->response->statusCode = 404;
            return $this->generateErrorResponse('Role not found.');
        } else {
            $auth->revokeAll($user->id);
        }

        if ($auth->assign($role, $user->id)) {
            Yii::$app->response->statusCode = 200;
            return ['status' => 'success', 'message'=>'Role assigned successfully.'];
        } else {
            Yii::$app->response->statusCode = 500;
            return $this->generateErrorResponse('Failed to assign role.');
        }
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested user does not exist.');
    }
    private function generateSuccessResponse($user, $roles)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'created_at' => $user->created_at,
            'roles' => array_keys($roles),
        ];
    }
    private function generateErrorResponse($message){
        return ['status' => 'error', 'message' => $message];
    }
}