<?php

use yii\db\Migration;

/**
 * Class m240101_000001_create_short_links_tables
 */
class m240101_000001_create_short_links_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Таблица коротких ссылок
        $this->createTable('{{%short_links}}', [
            'id' => $this->primaryKey(),
            'original_url' => $this->text()->notNull()->comment('Оригинальная ссылка'),
            'short_code' => $this->string(10)->notNull()->unique()->comment('Короткий код'),
            'qr_code_path' => $this->string(255)->comment('Путь к QR коду'),
            'clicks_count' => $this->integer()->defaultValue(0)->comment('Количество переходов'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->comment('Дата обновления'),
        ]);

        // Таблица логов переходов
        $this->createTable('{{%link_clicks}}', [
            'id' => $this->primaryKey(),
            'short_link_id' => $this->integer()->notNull()->comment('ID короткой ссылки'),
            'ip_address' => $this->string(45)->notNull()->comment('IP адрес'),
            'user_agent' => $this->text()->comment('User Agent'),
            'referer' => $this->text()->comment('Реферер'),
            'clicked_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->comment('Время перехода'),
        ]);

        // Индексы
        $this->createIndex('idx_short_links_short_code', '{{%short_links}}', 'short_code');
        $this->createIndex('idx_link_clicks_short_link_id', '{{%link_clicks}}', 'short_link_id');
        $this->createIndex('idx_link_clicks_clicked_at', '{{%link_clicks}}', 'clicked_at');

        // Внешний ключ
        $this->addForeignKey(
            'fk_link_clicks_short_link_id',
            '{{%link_clicks}}',
            'short_link_id',
            '{{%short_links}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_link_clicks_short_link_id', '{{%link_clicks}}');
        $this->dropTable('{{%link_clicks}}');
        $this->dropTable('{{%short_links}}');
    }
} 