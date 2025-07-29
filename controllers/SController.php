<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\ShortLink;
use app\models\LinkClick;

/**
 * Контроллер для обработки коротких ссылок
 */
class SController extends Controller
{
    /**
     * Переход по короткой ссылке
     *
     * @param string $code
     * @return \yii\web\Response
     */
    public function actionIndex($code)
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
} 