<?php
/**
 * Plugin Name: Advanced FAQ for WordPress
 * Plugin URI: https://fjomah.com
 * Description: إضافة الأسئلة الشائعة المتقدمة للوردبريس مع ويدجت يظهر في نهاية المقالات وسكيما متوافقة مع محركات البحث
 * Version: 1.0.0
 * Author: فوزي جمعة
 * Author URI: https://fjomah.com
 * Text Domain: advanced-faq-wp
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Contact Information:
 * Phone/WhatsApp: +201111933193
 * Email: info@fjomah.com
 * Website: fjomah.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AFAQ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFAQ_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AFAQ_VERSION', '1.0.0');

// Main plugin class
class AdvancedFAQWordPress {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('advanced-faq-wp', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        $this->init_hooks();
        $this->init_admin();
        $this->init_frontend();
    }
    
    private function init_hooks() {
        // Add meta box to posts
        add_action('add_meta_boxes', array($this, 'add_faq_meta_box'));
        add_action('save_post', array($this, 'save_faq_meta_box'));
        
        // Add FAQ widget to post content
        add_filter('the_content', array($this, 'add_faq_widget_to_content'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // AJAX handlers
        add_action('wp_ajax_afaq_save_faq', array($this, 'ajax_save_faq'));
        add_action('wp_ajax_afaq_delete_faq', array($this, 'ajax_delete_faq'));
    }
    
    private function init_admin() {
        // Admin functionality will be handled by hooks
    }
    
    private function init_frontend() {
        // Frontend functionality will be handled by hooks
    }
    
    public function activate() {
        // Create database table for FAQs
        $this->create_faq_table();
        
        // Set default options
        add_option('afaq_settings', array(
            'widget_title' => 'الأسئلة الشائعة',
            'widget_position' => 'after_content',
            'enable_schema' => true,
            'default_expanded' => false
        ));
    }
    
    public function deactivate() {
        // Cleanup if needed
    }
    
    private function create_faq_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'advanced_faq';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question text NOT NULL,
            answer longtext NOT NULL,
            sort_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'الأسئلة الشائعة',
            'الأسئلة الشائعة',
            'manage_options',
            'advanced-faq',
            array($this, 'admin_page'),
            'dashicons-editor-help',
            30
        );
        
        add_submenu_page(
            'advanced-faq',
            'إدارة الأسئلة',
            'إدارة الأسئلة',
            'manage_options',
            'advanced-faq',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'advanced-faq',
            'الإعدادات',
            'الإعدادات',
            'manage_options',
            'advanced-faq-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_page() {
        include AFAQ_PLUGIN_PATH . 'admin/admin-page.php';
    }
    
    public function settings_page() {
        include AFAQ_PLUGIN_PATH . 'admin/settings-page.php';
    }
    
    public function add_faq_meta_box() {
        add_meta_box(
            'afaq_meta_box',
            'إعدادات الأسئلة الشائعة',
            array($this, 'faq_meta_box_callback'),
            'post',
            'normal',
            'default'
        );
    }
    
    public function faq_meta_box_callback($post) {
        wp_nonce_field('afaq_meta_box', 'afaq_meta_box_nonce');
        
        $show_faq = get_post_meta($post->ID, '_afaq_show_faq', true);
        $faq_type = get_post_meta($post->ID, '_afaq_type', true);
        $custom_faqs = get_post_meta($post->ID, '_afaq_custom_faqs', true);
        
        if (!$faq_type) $faq_type = 'global';
        if (!$custom_faqs) $custom_faqs = array();
        
        ?>
        <div class="afaq-meta-box">
            <div class="afaq-option">
                <label for="afaq_show_faq">
                    <input type="checkbox" id="afaq_show_faq" name="afaq_show_faq" value="1" <?php checked($show_faq, '1'); ?> />
                    إظهار الأسئلة الشائعة في هذه المقالة
                </label>
            </div>
            
            <div class="afaq-type-selection" style="margin-top: 15px; <?php echo $show_faq !== '1' ? 'display:none;' : ''; ?>">
                <h4>نوع الأسئلة الشائعة:</h4>
                <label>
                    <input type="radio" name="afaq_type" value="global" <?php checked($faq_type, 'global'); ?> />
                    استخدام الأسئلة الجاهزة من الإعدادات
                </label><br>
                <label style="margin-top: 10px; display: block;">
                    <input type="radio" name="afaq_type" value="custom" <?php checked($faq_type, 'custom'); ?> />
                    أسئلة مخصصة لهذه المقالة
                </label>
            </div>
            
            <div class="afaq-custom-section" style="margin-top: 20px; <?php echo ($show_faq !== '1' || $faq_type !== 'custom') ? 'display:none;' : ''; ?>">
                <h4>الأسئلة المخصصة:</h4>
                <div id="afaq-custom-faqs">
                    <?php if (!empty($custom_faqs)): ?>
                        <?php foreach ($custom_faqs as $index => $faq): ?>
                            <div class="afaq-custom-item" data-index="<?php echo $index; ?>">
                                <div class="afaq-custom-question">
                                    <label>السؤال:</label>
                                    <input type="text" name="afaq_custom_faqs[<?php echo $index; ?>][question]" value="<?php echo esc_attr($faq['question']); ?>" placeholder="اكتب السؤال هنا..." style="width: 100%; margin-bottom: 10px;" />
                                </div>
                                <div class="afaq-custom-answer">
                                    <label>الإجابة:</label>
                                    <textarea name="afaq_custom_faqs[<?php echo $index; ?>][answer]" placeholder="اكتب الإجابة هنا..." style="width: 100%; height: 80px; margin-bottom: 10px;"><?php echo esc_textarea($faq['answer']); ?></textarea>
                                </div>
                                <button type="button" class="button afaq-remove-item" style="color: #dc3545;">حذف هذا السؤال</button>
                                <hr style="margin: 15px 0;">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="afaq-add-custom" class="button button-secondary">إضافة سؤال جديد</button>
            </div>
        </div>
        
        <style>
        .afaq-meta-box {
            padding: 10px 0;
        }
        .afaq-option label {
            font-weight: bold;
        }
        .afaq-custom-item {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .afaq-custom-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var faqIndex = <?php echo count($custom_faqs); ?>;
            
            $('#afaq_show_faq').change(function() {
                if ($(this).is(':checked')) {
                    $('.afaq-type-selection').show();
                    if ($('input[name="afaq_type"]:checked').val() === 'custom') {
                        $('.afaq-custom-section').show();
                    }
                } else {
                    $('.afaq-type-selection, .afaq-custom-section').hide();
                }
            });
            
            $('input[name="afaq_type"]').change(function() {
                if ($(this).val() === 'custom') {
                    $('.afaq-custom-section').show();
                } else {
                    $('.afaq-custom-section').hide();
                }
            });
            
            $('#afaq-add-custom').click(function() {
                var html = '<div class="afaq-custom-item" data-index="' + faqIndex + '">' +
                    '<div class="afaq-custom-question">' +
                    '<label>السؤال:</label>' +
                    '<input type="text" name="afaq_custom_faqs[' + faqIndex + '][question]" placeholder="اكتب السؤال هنا..." style="width: 100%; margin-bottom: 10px;" />' +
                    '</div>' +
                    '<div class="afaq-custom-answer">' +
                    '<label>الإجابة:</label>' +
                    '<textarea name="afaq_custom_faqs[' + faqIndex + '][answer]" placeholder="اكتب الإجابة هنا..." style="width: 100%; height: 80px; margin-bottom: 10px;"></textarea>' +
                    '</div>' +
                    '<button type="button" class="button afaq-remove-item" style="color: #dc3545;">حذف هذا السؤال</button>' +
                    '<hr style="margin: 15px 0;">' +
                    '</div>';
                $('#afaq-custom-faqs').append(html);
                faqIndex++;
            });
            
            $(document).on('click', '.afaq-remove-item', function() {
                $(this).closest('.afaq-custom-item').remove();
            });
        });
        </script>
        <?php
    }
    
    public function save_faq_meta_box($post_id) {
        if (!isset($_POST['afaq_meta_box_nonce']) || !wp_verify_nonce($_POST['afaq_meta_box_nonce'], 'afaq_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save show FAQ option
        $show_faq = isset($_POST['afaq_show_faq']) ? '1' : '0';
        update_post_meta($post_id, '_afaq_show_faq', $show_faq);
        
        // Save FAQ type
        $faq_type = isset($_POST['afaq_type']) ? sanitize_text_field($_POST['afaq_type']) : 'global';
        update_post_meta($post_id, '_afaq_type', $faq_type);
        
        // Save custom FAQs
        if (isset($_POST['afaq_custom_faqs']) && is_array($_POST['afaq_custom_faqs'])) {
            $custom_faqs = array();
            foreach ($_POST['afaq_custom_faqs'] as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $custom_faqs[] = array(
                        'question' => sanitize_text_field($faq['question']),
                        'answer' => wp_kses_post($faq['answer'])
                    );
                }
            }
            update_post_meta($post_id, '_afaq_custom_faqs', $custom_faqs);
        } else {
            delete_post_meta($post_id, '_afaq_custom_faqs');
        }
    }
    
    public function add_faq_widget_to_content($content) {
        if (!is_single() || !in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        global $post;
        $show_faq = get_post_meta($post->ID, '_afaq_show_faq', true);
        
        if ($show_faq === '1') {
            $settings = get_option('afaq_settings', array());
            $widget_position = isset($settings['widget_position']) ? $settings['widget_position'] : 'after_content';
            
            $faq_widget = $this->generate_faq_widget();
            
            if ($widget_position === 'before_content') {
                $content = $faq_widget . $content;
            } else {
                $content .= $faq_widget;
            }
        }
        
        return $content;
    }
    
    private function generate_faq_widget() {
        global $post, $wpdb;
        
        // Get FAQ type and custom FAQs for this post
        $faq_type = get_post_meta($post->ID, '_afaq_type', true);
        $custom_faqs = get_post_meta($post->ID, '_afaq_custom_faqs', true);
        
        if (!$faq_type) $faq_type = 'global';
        
        $faqs = array();
        
        if ($faq_type === 'custom' && !empty($custom_faqs)) {
            // Use custom FAQs for this post
            foreach ($custom_faqs as $index => $faq) {
                $faqs[] = (object) array(
                    'id' => 'custom_' . $index,
                    'question' => $faq['question'],
                    'answer' => $faq['answer']
                );
            }
        } else {
            // Use global FAQs from database
            $table_name = $wpdb->prefix . 'advanced_faq';
            $faqs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
        }
        
        if (empty($faqs)) {
            return '';
        }
        
        $settings = get_option('afaq_settings', array());
        $widget_title = isset($settings['widget_title']) ? $settings['widget_title'] : 'الأسئلة الشائعة';
        
        ob_start();
        ?>
        <div class="afaq-widget">
            <h3 class="afaq-title"><?php echo esc_html($widget_title); ?></h3>
            <div class="afaq-container">
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="afaq-item">
                    <div class="afaq-question" data-target="afaq-answer-<?php echo $faq->id; ?>">
                        <span class="afaq-question-text"><?php echo esc_html($faq->question); ?></span>
                        <span class="afaq-toggle"></span>
                    </div>
                    <div class="afaq-answer" id="afaq-answer-<?php echo $faq->id; ?>">
                        <div class="afaq-answer-content">
                            <?php echo wp_kses_post($faq->answer); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (isset($settings['enable_schema']) && $settings['enable_schema']): ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                <?php foreach ($faqs as $index => $faq): ?>
                {
                    "@type": "Question",
                    "name": <?php echo json_encode($faq->question); ?>,
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": <?php echo json_encode(strip_tags($faq->answer)); ?>
                    }
                }<?php echo ($index < count($faqs) - 1) ? ',' : ''; ?>
                <?php endforeach; ?>
            ]
        }
        </script>
        <?php endif; ?>
        
        <?php
        return ob_get_clean();
    }
    
    public function enqueue_frontend_assets() {
        if (is_single()) {
            global $post;
            $show_faq = get_post_meta($post->ID, '_afaq_show_faq', true);
            
            if ($show_faq === '1') {
                wp_enqueue_style('afaq-frontend', AFAQ_PLUGIN_URL . 'assets/css/frontend.css', array(), AFAQ_VERSION);
                wp_enqueue_script('afaq-frontend', AFAQ_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), AFAQ_VERSION, true);
                
                // Pass settings to JavaScript
                $settings = get_option('afaq_settings', array());
                wp_localize_script('afaq-frontend', 'afaqSettings', array(
                    'animationSpeed' => isset($settings['animation_speed']) ? $settings['animation_speed'] : 300,
                    'showQuestionNumbers' => isset($settings['show_question_numbers']) ? $settings['show_question_numbers'] : false,
                    'customCSS' => isset($settings['custom_css']) ? $settings['custom_css'] : ''
                ));
            }
        }
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'advanced-faq') !== false) {
            wp_enqueue_style('afaq-admin', AFAQ_PLUGIN_URL . 'assets/css/admin.css', array(), AFAQ_VERSION);
            wp_enqueue_script('afaq-admin', AFAQ_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), AFAQ_VERSION, true);
            
            wp_localize_script('afaq-admin', 'afaq_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('afaq_nonce')
            ));
        }
    }
    
    public function ajax_save_faq() {
        check_ajax_referer('afaq_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'advanced_faq';
        
        $id = intval($_POST['id']);
        $question = sanitize_text_field($_POST['question']);
        $answer = wp_kses_post($_POST['answer']);
        $sort_order = intval($_POST['sort_order']);
        
        if ($id > 0) {
            // Update existing FAQ
            $result = $wpdb->update(
                $table_name,
                array(
                    'question' => $question,
                    'answer' => $answer,
                    'sort_order' => $sort_order
                ),
                array('id' => $id),
                array('%s', '%s', '%d'),
                array('%d')
            );
        } else {
            // Insert new FAQ
            $result = $wpdb->insert(
                $table_name,
                array(
                    'question' => $question,
                    'answer' => $answer,
                    'sort_order' => $sort_order,
                    'status' => 'active'
                ),
                array('%s', '%s', '%d', '%s')
            );
            $id = $wpdb->insert_id;
        }
        
        if ($result !== false) {
            wp_send_json_success(array('id' => $id, 'message' => 'تم حفظ السؤال بنجاح'));
        } else {
            wp_send_json_error('حدث خطأ أثناء حفظ السؤال');
        }
    }
    
    public function ajax_delete_faq() {
        check_ajax_referer('afaq_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'advanced_faq';
        
        $id = intval($_POST['id']);
        
        $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        if ($result !== false) {
            wp_send_json_success('تم حذف السؤال بنجاح');
        } else {
            wp_send_json_error('حدث خطأ أثناء حذف السؤال');
        }
    }
}

// Initialize the plugin
new AdvancedFAQWordPress();
?>