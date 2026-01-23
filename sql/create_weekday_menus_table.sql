-- ============================================================================
-- weekday_menus（曜日別メニュー）テーブル
-- 月曜〜日曜の7種類のメニューを管理
-- 毎週同じ曜日は同じメニューを提供
-- ============================================================================

CREATE TABLE IF NOT EXISTS weekday_menus (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'メニューID',
    weekday TINYINT NOT NULL COMMENT '曜日（1=月曜, 7=日曜）',
    product_id INT NOT NULL COMMENT '商品ID',
    is_active TINYINT(1) DEFAULT 1 COMMENT '有効フラグ',
    special_note TEXT COMMENT '特記事項',
    effective_from DATE COMMENT '有効期間開始日',
    effective_to DATE COMMENT '有効期間終了日',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '作成日時',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新日時',

    -- 制約
    UNIQUE KEY unique_weekday (weekday),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,

    -- インデックス
    INDEX idx_weekday (weekday),
    INDEX idx_product_id (product_id),
    INDEX idx_is_active (is_active),

    -- チェック制約（MySQL 8.0+の場合。MySQL 5.7では無視される）
    CHECK (weekday >= 1 AND weekday <= 7)
) ENGINE=InnoDB COMMENT='曜日別メニュー';

-- ============================================================================
-- サンプルデータ挿入（月〜金の5日分）
-- 実際の商品IDは環境に応じて変更してください
-- ============================================================================

-- 月曜日: 唐揚げ弁当
INSERT INTO weekday_menus (weekday, product_id, is_active, special_note, effective_from)
VALUES (1, 312, 1, '週初めの元気メニュー！', '2025-01-01')
ON DUPLICATE KEY UPDATE
    product_id = VALUES(product_id),
    special_note = VALUES(special_note),
    is_active = VALUES(is_active);

-- 火曜日: 焼き魚弁当
INSERT INTO weekday_menus (weekday, product_id, is_active, special_note, effective_from)
VALUES (2, 313, 1, 'ヘルシーな焼き魚', '2025-01-01')
ON DUPLICATE KEY UPDATE
    product_id = VALUES(product_id),
    special_note = VALUES(special_note),
    is_active = VALUES(is_active);

-- 水曜日: 幕の内弁当
INSERT INTO weekday_menus (weekday, product_id, is_active, special_note, effective_from)
VALUES (3, 314, 1, '週の真ん中、バランス重視', '2025-01-01')
ON DUPLICATE KEY UPDATE
    product_id = VALUES(product_id),
    special_note = VALUES(special_note),
    is_active = VALUES(is_active);

-- 木曜日: ハンバーグ弁当
INSERT INTO weekday_menus (weekday, product_id, is_active, special_note, effective_from)
VALUES (4, 311, 1, '人気No.1メニュー', '2025-01-01')
ON DUPLICATE KEY UPDATE
    product_id = VALUES(product_id),
    special_note = VALUES(special_note),
    is_active = VALUES(is_active);

-- 金曜日: とんかつ弁当
INSERT INTO weekday_menus (weekday, product_id, is_active, special_note, effective_from)
VALUES (5, 310, 1, '週末前のご褒美', '2025-01-01')
ON DUPLICATE KEY UPDATE
    product_id = VALUES(product_id),
    special_note = VALUES(special_note),
    is_active = VALUES(is_active);

-- 完了メッセージ
SELECT '曜日別メニューテーブルの作成が完了しました。' as message;
SELECT 'サンプルデータ（月〜金）を挿入しました。' as message;
