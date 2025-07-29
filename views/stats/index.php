<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Статистика ссылок';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stats-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'original_url',
                'label' => 'Оригинальная ссылка',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        Html::encode(substr($model->original_url, 0, 50) . (strlen($model->original_url) > 50 ? '...' : '')),
                        $model->original_url,
                        ['target' => '_blank']
                    );
                },
            ],
            [
                'attribute' => 'short_code',
                'label' => 'Короткий код',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        $model->short_code,
                        $model->getShortUrl(),
                        ['target' => '_blank']
                    );
                },
            ],
            [
                'attribute' => 'clicks_count',
                'label' => 'Переходов',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a(
                        $model->clicks_count,
                        Url::to(['stats/view', 'id' => $model->id]),
                        ['class' => 'btn btn-sm btn-outline-primary']
                    );
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'format' => 'datetime',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a(
                            '<i class="bi bi-eye"></i>',
                            Url::to(['stats/view', 'id' => $model->id]),
                            ['title' => 'Просмотр статистики', 'class' => 'btn btn-sm btn-outline-info']
                        );
                    },
                ],
            ],
        ],
    ]); ?>
</div> 