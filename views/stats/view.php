<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $shortLink app\models\ShortLink */
/* @var $clicksDataProvider yii\data\ActiveDataProvider */

$this->title = 'Статистика ссылки: ' . $shortLink->short_code;
$this->params['breadcrumbs'][] = ['label' => 'Статистика', 'url' => ['stats/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="stats-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Информация о ссылке</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Оригинальная ссылка:</strong></td>
                            <td><?= Html::a(Html::encode($shortLink->original_url), $shortLink->original_url, ['target' => '_blank']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Короткая ссылка:</strong></td>
                            <td><?= Html::a($shortLink->getShortUrl(), $shortLink->getShortUrl(), ['target' => '_blank']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Короткий код:</strong></td>
                            <td><code><?= Html::encode($shortLink->short_code) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>Всего переходов:</strong></td>
                            <td><span class="badge bg-primary"><?= $shortLink->clicks_count ?></span></td>
                        </tr>
                        <tr>
                            <td><strong>Дата создания:</strong></td>
                            <td><?= Yii::$app->formatter->asDatetime($shortLink->created_at) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Последнее обновление:</strong></td>
                            <td><?= Yii::$app->formatter->asDatetime($shortLink->updated_at) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>QR код</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($shortLink->qr_code_path): ?>
                        <img src="<?= Html::encode($shortLink->qr_code_path) ?>" class="img-fluid" style="max-width: 200px; max-height: 200px;">
                        <br><br>
                        <a href="<?= Html::encode($shortLink->qr_code_path) ?>" download class="btn btn-outline-primary">
                            <i class="bi bi-download"></i> Скачать QR код
                        </a>
                    <?php else: ?>
                        <p class="text-muted">QR код не найден</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>История переходов</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $clicksDataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'ip_address',
                                'label' => 'IP адрес',
                            ],
                            [
                                'attribute' => 'user_agent',
                                'label' => 'User Agent',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return Html::tag('small', Html::encode(substr($model->user_agent, 0, 100) . (strlen($model->user_agent) > 100 ? '...' : '')));
                                },
                            ],
                            [
                                'attribute' => 'referer',
                                'label' => 'Реферер',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->referer) {
                                        return Html::a(
                                            Html::encode(substr($model->referer, 0, 50) . (strlen($model->referer) > 50 ? '...' : '')),
                                            $model->referer,
                                            ['target' => '_blank']
                                        );
                                    }
                                    return '<span class="text-muted">-</span>';
                                },
                            ],
                            [
                                'attribute' => 'clicked_at',
                                'label' => 'Время перехода',
                                'format' => 'datetime',
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div> 