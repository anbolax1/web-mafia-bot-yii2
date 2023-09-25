<?php

namespace app\controllers;

use app\models\Game;
use app\models\Guild;
use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\console\ExitCode;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        /*$guildId = 803807947066703883;
        $categoryId = 803807947532009473;

        $channelId = Yii::$app->bot->createTextChannel($guildId, $categoryId, 'игра istwood100');
        $threadId = Yii::$app->bot->createThread($channelId, 'ветка 1');
        $threadId2 = Yii::$app->bot->createThread($channelId, 'ветка 2');
        $threadId3 = Yii::$app->bot->createThread($channelId, 'ветка 3');

        $result = Yii::$app->bot->inviteUserToThread($threadId, 162954416528293889);
        $result = Yii::$app->bot->inviteUserToThread($threadId, 265322977732722688);
        $result = Yii::$app->bot->inviteUserToThread($threadId, 314102544219635715);
        $result = Yii::$app->bot->inviteUserToThread($threadId, 384737787976613899);
        $result = Yii::$app->bot->inviteUserToThread($threadId, 409771772607725578);
        sleep(3);
        $result = Yii::$app->bot->inviteUserToThread($threadId2, 162954416528293889);
        $result = Yii::$app->bot->inviteUserToThread($threadId2, 505834804713881612);
        $result = Yii::$app->bot->inviteUserToThread($threadId2, 535106333528293416);
        $result = Yii::$app->bot->inviteUserToThread($threadId2, 695728570894647426);
        $result = Yii::$app->bot->inviteUserToThread($threadId2, 994934004857982987);
        sleep(3);
        $result = Yii::$app->bot->inviteUserToThread($threadId3, 162954416528293889);
        $result = Yii::$app->bot->inviteUserToThread($threadId3, 505834804713881612);
        $result = Yii::$app->bot->inviteUserToThread($threadId3, 535106333528293416);
        $result = Yii::$app->bot->inviteUserToThread($threadId3, 695728570894647426);
        $result = Yii::$app->bot->inviteUserToThread($threadId3, 994934004857982987);*/


        $gameModel = new Game();
        $dataProvider = $gameModel->getGames(Yii::$app->request->queryParams);
        $games = Game::find()->where(['<>', 'status', Game::GAME_CANCELED]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLoginAsUser()
    {
        $userId = $_GET['id'];
        /**
         * @var $user User
         */
        $user = User::find()->where(['id' => $userId])->one();

        Yii::$app->user->login($user, 3600*24*30);

        return $this->render('index');
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionHome()
    {
        $get = $_GET;
    }

    public function actionProfile()
    {
        $user = Yii::$app->user->getIdentity();
        $discordUser = $user->getDiscordUser()->one();

        $gamesPlayed = Game::getPlayedGames($discordUser->discord_id);
        $gamesHosted = Game::getHostedGames($user->id);

        $gamesPlayedCount = count($gamesPlayed);
        $gamesHostedCount = count($gamesHosted);

        $gamesWonCount = 0;
        foreach ($gamesPlayed as $gamePlayed) {
            $memberRole = in_array($gamePlayed['role'], [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
            if($memberRole == $gamePlayed['win_role']){
                $gamesWonCount++;
            }
        }

        return $this->render('profile', [
            'user' => $user,
            'discordUser' => $discordUser,
            'gamesPlayedCount' => $gamesPlayedCount,
            'gamesWonCount' => $gamesWonCount,
            'gamesHostedCount' => $gamesHostedCount,
        ]);
    }
}
