<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\ShortLink;
use app\models\LinkClick;
use yii\data\ActiveDataProvider;

/**
 * Контроллер для просмотра статистики
 */
class StatsController extends Controller
{
    /**
     * Просмотр статистики по всем ссылкам
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ShortLink::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Детальная статистика по конкретной ссылке
     *
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        $shortLink = ShortLink::findOne($id);
        
        if (!$shortLink) {
            throw new \yii\web\NotFoundHttpException('Ссылка не найдена');
        }

        $clicksDataProvider = new ActiveDataProvider([
            'query' => LinkClick::find()->where(['short_link_id' => $id])->orderBy(['clicked_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('view', [
            'shortLink' => $shortLink,
            'clicksDataProvider' => $clicksDataProvider,
        ]);
    }
} 