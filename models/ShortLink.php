<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;
use GuzzleHttp\Client;

class ShortLink extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%short_links}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['original_url', 'short_code'], 'required'],
            [['original_url'], 'string'],
            [['clicks_count'], 'integer'],
            [['clicks_count'], 'default', 'value' => 0],
            [['short_code'], 'string', 'max' => 10],
            [['qr_code_path'], 'string', 'max' => 255],
            [['short_code'], 'unique'],
            [['original_url'], 'url'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'original_url' => 'Оригинальная ссылка',
            'short_code' => 'Короткий код',
            'qr_code_path' => 'QR код',
            'clicks_count' => 'Количество переходов',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    public function getLinkClicks()
    {
        return $this->hasMany(LinkClick::class, ['short_link_id' => 'id']);
    }

    public static function generateShortCode()
    {
        do {
            $code = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        } while (self::findOne(['short_code' => $code]));

        return $code;
    }

    public static function findByCode($code)
    {
        return self::findOne(['short_code' => $code]);
    }

    public function incrementClicks()
    {
        $this->clicks_count++;
        $this->save(false);
    }

    public function getShortUrl()
    {
        $host = Yii::$app->request->hostInfo;
        
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $host = 'http://192.168.1.7';
        }
        
        return $host . '/s/' . $this->short_code;
    }

    public static function validateAndCheckUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'message' => 'Неверный формат URL'
            ];
        }

        try {
            $client = new Client([
                'timeout' => 10,
                'verify' => false,
                'allow_redirects' => true,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]);

            $response = $client->get($url);
            
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
                return [
                    'success' => true,
                    'message' => 'URL доступен'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Данный URL не доступен (HTTP ' . $response->getStatusCode() . ')'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Данный URL не доступен'
            ];
        }
    }
} 