<?php

namespace app\controllers;

use app\models\DiscordUser;
use app\models\Game;
use app\models\GameMember;
use app\models\Guild;
use app\models\MemberRating;
use app\models\MemberRatingHistory;
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
//        $result = Yii::$app->bot->banMember(803807947066703883, 311591374791245824);
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
        if (!Yii::$app->user->getIdentity()->isAdmin()) {
            return $this->goHome();
        }
        $userId = $_GET['id'];
        /**
         * @var $user User
         */
        $user = User::find()->where(['id' => $userId])->one();

        Yii::$app->user->login($user, 3600*24*30);

        $gameModel = new Game();
        $dataProvider = $gameModel->getGames(Yii::$app->request->queryParams);
        $games = Game::find()->where(['<>', 'status', Game::GAME_CANCELED]);
        return $this->render('index', ['dataProvider' => $dataProvider]);
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
        if(!empty($_GET) && !empty($_GET['discord_id'])){
            $discordUserId = $_GET['discord_id'];
            $discordUser = DiscordUser::find()->where(['discord_id' => $discordUserId])->one();
            if(!empty($discordUser)){
                $user = User::find()->where(['id' => $discordUser->user_id])->one();
            } else {
                $discordUser = GameMember::find()->where(['discord_id' => $discordUserId])->one();
            }
        } else {
            $user = Yii::$app->user->getIdentity();
            $discordUser = $user->getDiscordUser()->one();
            $discordUserId = $discordUser->discord_id;
        }

        $discordUserName = !empty($discordUser->username) ? $discordUser->username : $discordUser->name;
        $discordUserAvatar = $discordUser->avatar;


        $gamesPlayed = Game::getPlayedGames($discordUserId);
        $gamesHosted = !empty($user) ? Game::getHostedGames($user->id) : [];

        $gamesPlayedCount = count($gamesPlayed);
        $gamesHostedCount = count($gamesHosted);

        $gamesWonCount = 0;

        $gamesGeneralMirPlayedCount = 0; //общее число игр на красном (включая шерифа)
        $gamesGeneralMafPlayedCount = 0; //общее число игр на черном (включая дона)

        $gamesGeneralMirWinCount = 0;
        $gamesGeneralMafWinCount = 0;

        $gamesMirPlayedCount = 0;
        $gamesSheriffPlayedCount = 0;
        $gamesMafPlayedCount = 0;
        $gamesDonPlayedCount = 0;

        $gamesWinMirCount = 0;
        $gamesWinSheriffCount = 0;
        $gamesWinMafCount = 0;
        $gamesWinDonCount = 0;

        $playedDays = [];
        $firstGameDate = '';
        $lastGameDate = '';

        if(!empty($gamesPlayedCount)) {
            foreach ($gamesPlayed as $gamePlayed) {
                $memberRole = in_array($gamePlayed['role'], [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
                if($memberRole == $gamePlayed['win_role']){
                    $gamesWonCount++;
                }

                switch ($gamePlayed['role']) {
                    case Game::ROLE_MIR:
                        $gamesMirPlayedCount++;
                        $gamesGeneralMirPlayedCount++;
                        break;
                    case Game::ROLE_SHERIFF:
                        $gamesSheriffPlayedCount++;
                        $gamesGeneralMirPlayedCount++;
                        break;
                    case Game::ROLE_MAF:
                        $gamesMafPlayedCount++;
                        $gamesGeneralMafPlayedCount++;
                        break;
                    case Game::ROLE_DON:
                        $gamesDonPlayedCount++;
                        $gamesGeneralMafPlayedCount++;
                        break;
                }

                if($gamePlayed['win_role'] == Game::ROLE_MIR) {
                    switch ($gamePlayed['role']) {
                        case Game::ROLE_MIR:
                            $gamesWinMirCount++;
                            $gamesGeneralMirWinCount++;
                            break;
                        case Game::ROLE_SHERIFF:
                            $gamesWinSheriffCount++;
                            $gamesGeneralMirWinCount++;
                            break;
                    }
                }
                if($gamePlayed['win_role'] == Game::ROLE_MAF) {
                    switch ($gamePlayed['role']) {
                        case Game::ROLE_MAF:
                            $gamesWinMafCount++;
                            $gamesGeneralMafWinCount++;
                            break;
                        case Game::ROLE_DON:
                            $gamesWinDonCount++;
                            $gamesGeneralMafWinCount++;
                            break;
                    }
                }

                $gameStartDate = gmdate("d.m.Y", $gamePlayed['start_time']);
                if(empty($firstGameDate)){
                    $firstGameDate = $gameStartDate;
                }
                $lastGameDate = $gameStartDate;

                if(!in_array($gameStartDate, $playedDays)){
                    $playedDays[] = $gameStartDate;
                }
            }
        }

        $discordUserAvatar = strpos($discordUserAvatar, 'https') === false ? "https://cdn.discordapp.com/avatars/$discordUserId/$discordUserAvatar.jpg" : $discordUserAvatar;

        //получаем рейтинг (текущий, максимальный, минимальный)
        $currentRating = 1000;
        $maxRating = 1000;
        $minRating = 1000;

        $memberRatingHistory = MemberRatingHistory::find()->where(['discord_id' => $discordUserId, 'type' => MemberRating::RATING_GENERAL])->all();

        if(!empty($memberRatingHistory)){
            foreach ($memberRatingHistory as $memberRatingHistoryItem) {
                $currentRating = $currentRating + intval($memberRatingHistoryItem->change_rating);
                if($currentRating > $maxRating) {
                     $maxRating = $currentRating;
                }
                if($currentRating < $minRating) {
                    $minRating = $currentRating;
                }
            }
        }

        //получаем процент побед (общий, на красном, на чёрном

        $generalWinPercent = !empty($gamesPlayedCount) ? round($gamesWonCount / $gamesPlayedCount * 100, 0) : 0;

        $mirWinPercent = !empty($gamesMirPlayedCount) ? round($gamesWinMirCount / $gamesMirPlayedCount * 100, 0) : 0;
        $sheriffWinPercent = !empty($gamesSheriffPlayedCount) ? round($gamesWinSheriffCount / $gamesSheriffPlayedCount * 100, 0) : 0;
        $mafWinPercent = !empty($gamesMafPlayedCount) ? round($gamesWinMafCount / $gamesMafPlayedCount * 100, 0) : 0;
        $donWinPercent = !empty($gamesDonPlayedCount) ? round($gamesWinDonCount / $gamesDonPlayedCount * 100, 0) : 0;

        $generalMirWinPercent = !empty($gamesGeneralMirPlayedCount) ? round($gamesGeneralMirWinCount / $gamesGeneralMirPlayedCount * 100, 0) : 0;
        $generalMafWinPercent = !empty($gamesGeneralMafPlayedCount) ? round($gamesGeneralMafWinCount / $gamesGeneralMafPlayedCount * 100, 0) : 0;

        //получаем игровой стаж (игровых дней, дата первой игры, дата последней игры)

        return $this->render('profile', [
//            'user' => $user,
//            'discordUser' => $discordUser,
            'discordUserId' => $discordUserId,
            'discordUserName' => $discordUserName,
            'discordUserAvatar' => $discordUserAvatar,
            'gamesPlayedCount' => $gamesPlayedCount,
            'gamesWonCount' => $gamesWonCount,
            'gamesHostedCount' => $gamesHostedCount,
            'rating' => [
                'current' => $currentRating,
                'max' => $maxRating,
                'min' => $minRating
            ],
            'winPercent' => [
                'general' => $generalWinPercent,
                'mir' =>$generalMirWinPercent,
                'maf' => $generalMafWinPercent
            ],
            'gameExperience' => [
                'playedDays' => count($playedDays),
                'firstGameDate' => $firstGameDate,
                'lastGameDate' => $lastGameDate,
            ]
        ]);
    }
}
