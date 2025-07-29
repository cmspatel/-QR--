<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\ShortLink;
use app\models\LinkClick;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use yii\web\Response;

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
        return $this->render('index');
    }

    /**
     * Создает короткую ссылку и QR код
     *
     * @return array
     */
    public function actionCreateShortLink()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $url = Yii::$app->request->post('url');

        if (empty($url)) {
            return [
                'success' => false,
                'message' => 'URL не может быть пустым'
            ];
        }

        // Валидация и проверка доступности URL
        $validation = ShortLink::validateAndCheckUrl($url);
        if (!$validation['success']) {
            return $validation;
        }

        // Проверяем, не существует ли уже такая ссылка
        $existingLink = ShortLink::findOne(['original_url' => $url]);
        if ($existingLink) {
            return [
                'success' => true,
                'message' => 'Ссылка уже существует',
                'shortUrl' => $existingLink->getShortUrl(),
                'qrCode' => $existingLink->qr_code_path,
                'clicksCount' => $existingLink->clicks_count
            ];
        }

        // Создаем новую короткую ссылку
        $shortLink = new ShortLink();
        $shortLink->original_url = $url;
        $shortLink->short_code = ShortLink::generateShortCode();

        if ($shortLink->save()) {
            try {
                // Генерируем QR код
                $qrCodePath = $this->generateQrCode($shortLink->getShortUrl(), $shortLink->short_code);
                
                if ($qrCodePath) {
                    $shortLink->qr_code_path = $qrCodePath;
                    $shortLink->save();
                    
                    return [
                        'success' => true,
                        'message' => 'Короткая ссылка создана успешно',
                        'shortUrl' => $shortLink->getShortUrl(),
                        'qrCode' => $qrCodePath,
                        'clicksCount' => 0
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => 'Короткая ссылка создана успешно (QR код не сгенерирован)',
                        'shortUrl' => $shortLink->getShortUrl(),
                        'qrCode' => null,
                        'clicksCount' => 0
                    ];
                }
            } catch (\Exception $e) {
                Yii::error('Ошибка генерации QR кода: ' . $e->getMessage());
                return [
                    'success' => true,
                    'message' => 'Короткая ссылка создана успешно (QR код не сгенерирован)',
                    'shortUrl' => $shortLink->getShortUrl(),
                    'qrCode' => null,
                    'clicksCount' => 0
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Ошибка при создании короткой ссылки'
        ];
    }

    /**
     * Генерирует QR код для ссылки
     *
     * @param string $url
     * @param string $code
     * @return string
     */
    private function generateQrCode($url, $code)
    {
        try {
            // Создаем QR код для версии 4.x в SVG формате
            $qrCode = new QrCode($url);
            $qrCode->setSize(300);
            $qrCode->setMargin(10);

            $writer = new SvgWriter();
            $result = $writer->write($qrCode);

            $qrDir = Yii::getAlias('@webroot/qr-codes');
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0777, true);
            }

            $filename = $code . '.svg';
            $filepath = $qrDir . '/' . $filename;
            
            // Сохраняем файл
            $result->saveToFile($filepath);

            // Проверяем, что файл создался
            if (file_exists($filepath)) {
                return '/qr-codes/' . $filename;
            } else {
                Yii::error('QR код не был создан: ' . $filepath);
                return null;
            }
        } catch (\Exception $e) {
            Yii::error('Ошибка генерации QR кода: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Переход по короткой ссылке
     *
     * @param string $code
     * @return \yii\web\Response
     */
    public function actionRedirect($code)
    {
        $shortLink = ShortLink::findByCode($code);
        
        if (!$shortLink) {
            throw new \yii\web\NotFoundHttpException('Ссылка не найдена');
        }

        // Логируем переход
        LinkClick::logClick($shortLink->id);
        
        // Увеличиваем счетчик
        $shortLink->incrementClicks();

        return $this->redirect($shortLink->original_url);
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
}
