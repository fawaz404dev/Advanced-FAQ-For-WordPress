<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle settings save
if (isset($_POST['save_settings'])) {
    if (wp_verify_nonce($_POST['afaq_settings_nonce'], 'afaq_save_settings')) {
        $settings = array(
            'widget_title' => sanitize_text_field($_POST['widget_title']),
            'widget_position' => sanitize_text_field($_POST['widget_position']),
            'enable_schema' => isset($_POST['enable_schema']) ? true : false,
            'default_expanded' => isset($_POST['default_expanded']) ? true : false,
            'show_question_numbers' => isset($_POST['show_question_numbers']) ? true : false,
            'animation_speed' => intval($_POST['animation_speed']),
            'custom_css' => wp_strip_all_tags($_POST['custom_css'])
        );
        
        update_option('afaq_settings', $settings);
        echo '<div class="notice notice-success"><p>تم حفظ الإعدادات بنجاح!</p></div>';
    }
}

// Get current settings
$settings = get_option('afaq_settings', array(
    'widget_title' => 'الأسئلة الشائعة',
    'widget_position' => 'after_content',
    'enable_schema' => true,
    'default_expanded' => false,
    'show_question_numbers' => false,
    'animation_speed' => 300,
    'custom_css' => ''
));
?>

<div class="wrap">
    <h1>إعدادات الأسئلة الشائعة</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('afaq_save_settings', 'afaq_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="widget_title">عنوان الويدجت</label>
                </th>
                <td>
                    <input type="text" id="widget_title" name="widget_title" value="<?php echo esc_attr($settings['widget_title']); ?>" class="regular-text" />
                    <p class="description">العنوان الذي سيظهر أعلى قسم الأسئلة الشائعة</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="widget_position">موضع الويدجت</label>
                </th>
                <td>
                    <select id="widget_position" name="widget_position">
                        <option value="after_content" <?php selected($settings['widget_position'], 'after_content'); ?>>بعد محتوى المقالة</option>
                        <option value="before_content" <?php selected($settings['widget_position'], 'before_content'); ?>>قبل محتوى المقالة</option>
                    </select>
                    <p class="description">اختر مكان ظهور الأسئلة الشائعة في المقالة</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">خيارات العرض</th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="enable_schema" value="1" <?php checked($settings['enable_schema'], true); ?> />
                            تفعيل سكيما محركات البحث (Schema.org)
                        </label>
                        <br><br>
                        
                        <label>
                            <input type="checkbox" name="default_expanded" value="1" <?php checked($settings['default_expanded'], true); ?> />
                            إظهار الإجابات مفتوحة افتراضياً
                        </label>
                        <br><br>
                        
                        <label>
                            <input type="checkbox" name="show_question_numbers" value="1" <?php checked($settings['show_question_numbers'], true); ?> />
                            إظهار أرقام الأسئلة
                        </label>
                    </fieldset>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="animation_speed">سرعة الحركة (بالميلي ثانية)</label>
                </th>
                <td>
                    <input type="number" id="animation_speed" name="animation_speed" value="<?php echo esc_attr($settings['animation_speed']); ?>" min="0" max="2000" class="small-text" />
                    <p class="description">سرعة فتح وإغلاق الإجابات (0 = بدون حركة، 300 = متوسط، 600 = بطيء)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="custom_css">CSS مخصص</label>
                </th>
                <td>
                    <textarea id="custom_css" name="custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                    <p class="description">أضف CSS مخصص لتخصيص مظهر الأسئلة الشائعة</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('حفظ الإعدادات', 'primary', 'save_settings'); ?>
    </form>
    
    <hr>
    
    <div class="afaq-info-section">
        <h2>معلومات الإضافة</h2>
        <div class="afaq-info-grid">
            <div class="afaq-info-card">
                <h3>كيفية الاستخدام</h3>
                <ol>
                    <li>أضف الأسئلة الشائعة من صفحة "إدارة الأسئلة"</li>
                    <li>عند كتابة أو تعديل مقالة، ستجد مربع "إعدادات الأسئلة الشائعة" في الشريط الجانبي</li>
                    <li>فعّل خيار "إظهار الأسئلة الشائعة في هذه المقالة"</li>
                    <li>احفظ المقالة وستظهر الأسئلة الشائعة تلقائياً</li>
                </ol>
            </div>
            
            <div class="afaq-info-card">
                <h3>مميزات الإضافة</h3>
                <ul>
                    <li>✅ سكيما متوافقة 100% مع محركات البحث</li>
                    <li>✅ تصميم متجاوب وجميل</li>
                    <li>✅ سهولة الإدارة والتحكم</li>
                    <li>✅ إمكانية التخصيص الكامل</li>
                    <li>✅ دعم اللغة العربية</li>
                </ul>
            </div>
            
            <div class="afaq-info-card">
                <h3>معلومات المطور</h3>
                <p><strong>الاسم:</strong> فوزي جمعة</p>
                <p><strong>الهاتف/واتساب:</strong> +201111933193</p>
                <p><strong>الموقع:</strong> <a href="https://fjomah.com" target="_blank">fjomah.com</a></p>
                <p><strong>البريد الإلكتروني:</strong> <a href="mailto:info@fjomah.com">info@fjomah.com</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.afaq-info-section {
    margin-top: 30px;
}

.afaq-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.afaq-info-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.afaq-info-card h3 {
    margin-top: 0;
    color: #23282d;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.afaq-info-card ul,
.afaq-info-card ol {
    margin: 15px 0;
}

.afaq-info-card li {
    margin-bottom: 8px;
    line-height: 1.5;
}

.afaq-info-card a {
    color: #0073aa;
    text-decoration: none;
}

.afaq-info-card a:hover {
    text-decoration: underline;
}
</style>