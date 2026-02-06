/**
 * Smiley Kitchen 共通JavaScript
 * billing-system と order で共通利用
 */

// 共通ユーティリティ
const SmileyCommon = {
    /**
     * 日付フォーマット
     * @param {string|Date} date - 日付
     * @param {string} format - フォーマット（default: 'YYYY-MM-DD'）
     * @returns {string}
     */
    formatDate: function(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');

        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    },

    /**
     * 金額フォーマット
     * @param {number} amount - 金額
     * @returns {string}
     */
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('ja-JP', {
            style: 'currency',
            currency: 'JPY'
        }).format(amount);
    },

    /**
     * 数値フォーマット
     * @param {number} num - 数値
     * @returns {string}
     */
    formatNumber: function(num) {
        return new Intl.NumberFormat('ja-JP').format(num);
    },

    /**
     * API呼び出し
     * @param {string} url - APIエンドポイント
     * @param {object} options - fetch options
     * @returns {Promise}
     */
    api: async function(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json'
            }
        };

        const mergedOptions = { ...defaultOptions, ...options };

        if (mergedOptions.body && typeof mergedOptions.body === 'object') {
            mergedOptions.body = JSON.stringify(mergedOptions.body);
        }

        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'APIエラーが発生しました');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    /**
     * トースト通知表示
     * @param {string} message - メッセージ
     * @param {string} type - タイプ（success, error, warning, info）
     * @param {number} duration - 表示時間（ms）
     */
    toast: function(message, type = 'info', duration = 3000) {
        // 既存のトーストを削除
        const existingToast = document.querySelector('.smiley-toast');
        if (existingToast) {
            existingToast.remove();
        }

        // トースト要素を作成
        const toast = document.createElement('div');
        toast.className = `smiley-toast smiley-toast-${type}`;
        toast.textContent = message;

        // スタイルを適用
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '14px 20px',
            borderRadius: '8px',
            color: 'white',
            fontWeight: '500',
            zIndex: '9999',
            animation: 'slideIn 0.3s ease',
            maxWidth: '400px'
        });

        // タイプ別の背景色
        const colors = {
            success: '#4CAF50',
            error: '#F44336',
            warning: '#FF9800',
            info: '#2196F3'
        };
        toast.style.backgroundColor = colors[type] || colors.info;

        // DOMに追加
        document.body.appendChild(toast);

        // 自動で消える
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    /**
     * 確認ダイアログ
     * @param {string} message - メッセージ
     * @param {string} title - タイトル
     * @returns {Promise<boolean>}
     */
    confirm: function(message, title = '確認') {
        return new Promise((resolve) => {
            // シンプルな確認ダイアログ
            const result = window.confirm(message);
            resolve(result);
        });
    },

    /**
     * ローディング表示
     * @param {boolean} show - 表示/非表示
     */
    loading: function(show) {
        let overlay = document.querySelector('.smiley-loading-overlay');

        if (show) {
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'smiley-loading-overlay';
                overlay.innerHTML = '<div class="spinner"></div>';

                Object.assign(overlay.style, {
                    position: 'fixed',
                    top: '0',
                    left: '0',
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'rgba(255,255,255,0.8)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: '9998'
                });

                document.body.appendChild(overlay);
            }
        } else {
            if (overlay) {
                overlay.remove();
            }
        }
    },

    /**
     * デバウンス
     * @param {Function} func - 実行する関数
     * @param {number} wait - 待機時間（ms）
     * @returns {Function}
     */
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * 曜日を日本語で取得
     * @param {Date|string} date - 日付
     * @returns {string}
     */
    getDayOfWeekJp: function(date) {
        const days = ['日', '月', '火', '水', '木', '金', '土'];
        const d = new Date(date);
        return days[d.getDay()];
    },

    /**
     * フォームデータをオブジェクトに変換
     * @param {HTMLFormElement} form - フォーム要素
     * @returns {object}
     */
    formToObject: function(form) {
        const formData = new FormData(form);
        const obj = {};
        formData.forEach((value, key) => {
            obj[key] = value;
        });
        return obj;
    },

    /**
     * 入力値のサニタイズ
     * @param {string} str - 入力文字列
     * @returns {string}
     */
    sanitize: function(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

// CSSアニメーションを追加
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// グローバルに公開
window.SmileyCommon = SmileyCommon;
