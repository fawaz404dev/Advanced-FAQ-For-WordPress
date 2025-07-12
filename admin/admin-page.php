<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'advanced_faq';

// Handle form submissions
if (isset($_POST['submit_faq'])) {
    $question = sanitize_text_field($_POST['question']);
    $answer = wp_kses_post($_POST['answer']);
    $sort_order = intval($_POST['sort_order']);
    
    if (!empty($question) && !empty($answer)) {
        $wpdb->insert(
            $table_name,
            array(
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $sort_order,
                'status' => 'active'
            ),
            array('%s', '%s', '%d', '%s')
        );
        echo '<div class="notice notice-success"><p>تم إضافة السؤال بنجاح!</p></div>';
    }
}

// Get all FAQs
$faqs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY sort_order ASC, id ASC");
?>

<div class="wrap">
    <h1>إدارة الأسئلة الشائعة</h1>
    
    <div class="afaq-admin-container">
        <!-- Add New FAQ Form -->
        <div class="afaq-form-section">
            <h2>إضافة سؤال جديد</h2>
            <form method="post" action="" class="afaq-form">
                <?php wp_nonce_field('afaq_admin_action', 'afaq_admin_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="question">السؤال</label>
                        </th>
                        <td>
                            <input type="text" id="question" name="question" class="regular-text" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="answer">الإجابة</label>
                        </th>
                        <td>
                            <?php
                            wp_editor('', 'answer', array(
                                'textarea_name' => 'answer',
                                'textarea_rows' => 5,
                                'media_buttons' => false,
                                'teeny' => true,
                                'quicktags' => true
                            ));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sort_order">ترتيب العرض</label>
                        </th>
                        <td>
                            <input type="number" id="sort_order" name="sort_order" value="0" min="0" class="small-text" />
                            <p class="description">الرقم الأصغر يظهر أولاً</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('إضافة السؤال', 'primary', 'submit_faq'); ?>
            </form>
        </div>
        
        <!-- Existing FAQs List -->
        <div class="afaq-list-section">
            <h2>الأسئلة الموجودة</h2>
            
            <?php if (empty($faqs)): ?>
                <p>لا توجد أسئلة شائعة حتى الآن. قم بإضافة السؤال الأول!</p>
            <?php else: ?>
                <div class="afaq-items-container">
                    <?php foreach ($faqs as $faq): ?>
                        <div class="afaq-item-admin" data-id="<?php echo $faq->id; ?>">
                            <div class="afaq-item-header">
                                <h3 class="afaq-question-title"><?php echo esc_html($faq->question); ?></h3>
                                <div class="afaq-item-actions">
                                    <button type="button" class="button afaq-edit-btn" data-id="<?php echo $faq->id; ?>">تعديل</button>
                                    <button type="button" class="button afaq-delete-btn" data-id="<?php echo $faq->id; ?>">حذف</button>
                                    <span class="afaq-sort-order">الترتيب: <?php echo $faq->sort_order; ?></span>
                                </div>
                            </div>
                            
                            <div class="afaq-item-content">
                                <div class="afaq-answer-preview">
                                    <?php echo wp_trim_words(strip_tags($faq->answer), 20, '...'); ?>
                                </div>
                                
                                <!-- Edit Form (Hidden by default) -->
                                <div class="afaq-edit-form" id="edit-form-<?php echo $faq->id; ?>" style="display: none;">
                                    <form class="afaq-update-form">
                                        <input type="hidden" name="faq_id" value="<?php echo $faq->id; ?>" />
                                        
                                        <div class="form-field">
                                            <label>السؤال:</label>
                                            <input type="text" name="edit_question" value="<?php echo esc_attr($faq->question); ?>" class="regular-text" required />
                                        </div>
                                        
                                        <div class="form-field">
                                            <label>الإجابة:</label>
                                            <textarea name="edit_answer" rows="4" class="large-text" required><?php echo esc_textarea($faq->answer); ?></textarea>
                                        </div>
                                        
                                        <div class="form-field">
                                            <label>ترتيب العرض:</label>
                                            <input type="number" name="edit_sort_order" value="<?php echo $faq->sort_order; ?>" min="0" class="small-text" />
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button type="submit" class="button button-primary">حفظ التغييرات</button>
                                            <button type="button" class="button afaq-cancel-edit">إلغاء</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Edit FAQ
    $('.afaq-edit-btn').on('click', function() {
        var faqId = $(this).data('id');
        var editForm = $('#edit-form-' + faqId);
        var preview = $(this).closest('.afaq-item-admin').find('.afaq-answer-preview');
        
        if (editForm.is(':visible')) {
            editForm.hide();
            preview.show();
            $(this).text('تعديل');
        } else {
            editForm.show();
            preview.hide();
            $(this).text('إخفاء');
        }
    });
    
    // Cancel edit
    $('.afaq-cancel-edit').on('click', function() {
        var editForm = $(this).closest('.afaq-edit-form');
        var preview = editForm.siblings('.afaq-answer-preview');
        var editBtn = editForm.closest('.afaq-item-admin').find('.afaq-edit-btn');
        
        editForm.hide();
        preview.show();
        editBtn.text('تعديل');
    });
    
    // Update FAQ via AJAX
    $('.afaq-update-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = {
            action: 'afaq_save_faq',
            nonce: afaq_ajax.nonce,
            id: form.find('input[name="faq_id"]').val(),
            question: form.find('input[name="edit_question"]').val(),
            answer: form.find('textarea[name="edit_answer"]').val(),
            sort_order: form.find('input[name="edit_sort_order"]').val()
        };
        
        $.post(afaq_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('حدث خطأ: ' + response.data);
            }
        });
    });
    
    // Delete FAQ
    $('.afaq-delete-btn').on('click', function() {
        if (!confirm('هل أنت متأكد من حذف هذا السؤال؟')) {
            return;
        }
        
        var faqId = $(this).data('id');
        var faqItem = $(this).closest('.afaq-item-admin');
        
        var formData = {
            action: 'afaq_delete_faq',
            nonce: afaq_ajax.nonce,
            id: faqId
        };
        
        $.post(afaq_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                faqItem.fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert('حدث خطأ: ' + response.data);
            }
        });
    });
});
</script>