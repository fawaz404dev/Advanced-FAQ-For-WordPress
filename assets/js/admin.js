/**
 * Advanced FAQ for WordPress - Admin JavaScript
 * Author: فوزي جمعة
 * Website: fjomah.com
 */

(function($) {
    'use strict';
    
    // Admin FAQ Manager Class
    class AFAQAdmin {
        constructor() {
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.initSortable();
            this.initTooltips();
        }
        
        bindEvents() {
            // Edit FAQ button
            $(document).on('click', '.afaq-edit-btn', this.toggleEditForm.bind(this));
            
            // Cancel edit button
            $(document).on('click', '.afaq-cancel-edit', this.cancelEdit.bind(this));
            
            // Update FAQ form submission
            $(document).on('submit', '.afaq-update-form', this.updateFAQ.bind(this));
            
            // Delete FAQ button
            $(document).on('click', '.afaq-delete-btn', this.deleteFAQ.bind(this));
            
            // Form validation
            $(document).on('input', '.afaq-form input, .afaq-form textarea', this.validateForm.bind(this));
            
            // Auto-save draft functionality
            $(document).on('input', '.afaq-update-form input, .afaq-update-form textarea', 
                this.debounce(this.autoSave.bind(this), 2000));
        }
        
        toggleEditForm(event) {
            event.preventDefault();
            
            const $btn = $(event.currentTarget);
            const faqId = $btn.data('id');
            const $editForm = $('#edit-form-' + faqId);
            const $preview = $btn.closest('.afaq-item-admin').find('.afaq-answer-preview');
            
            if ($editForm.is(':visible')) {
                // Hide edit form
                $editForm.slideUp(300);
                $preview.slideDown(300);
                $btn.text('تعديل').removeClass('active');
            } else {
                // Show edit form
                $preview.slideUp(300);
                $editForm.slideDown(300);
                $btn.text('إخفاء').addClass('active');
                
                // Focus on first input
                setTimeout(() => {
                    $editForm.find('input[name="edit_question"]').focus();
                }, 350);
            }
        }
        
        cancelEdit(event) {
            event.preventDefault();
            
            const $editForm = $(event.currentTarget).closest('.afaq-edit-form');
            const $preview = $editForm.siblings('.afaq-answer-preview');
            const $editBtn = $editForm.closest('.afaq-item-admin').find('.afaq-edit-btn');
            
            // Reset form to original values
            this.resetForm($editForm);
            
            // Hide edit form
            $editForm.slideUp(300);
            $preview.slideDown(300);
            $editBtn.text('تعديل').removeClass('active');
        }
        
        updateFAQ(event) {
            event.preventDefault();
            
            const $form = $(event.currentTarget);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Validate form
            if (!this.validateUpdateForm($form)) {
                return;
            }
            
            // Show loading state
            $submitBtn.text('جاري الحفظ...').prop('disabled', true);
            $form.addClass('afaq-loading');
            
            const formData = {
                action: 'afaq_save_faq',
                nonce: afaq_ajax.nonce,
                id: $form.find('input[name="faq_id"]').val(),
                question: $form.find('input[name="edit_question"]').val().trim(),
                answer: $form.find('textarea[name="edit_answer"]').val().trim(),
                sort_order: $form.find('input[name="edit_sort_order"]').val()
            };
            
            $.post(afaq_ajax.ajax_url, formData)
                .done((response) => {
                    if (response.success) {
                        this.showMessage('تم حفظ التغييرات بنجاح!', 'success');
                        
                        // Update the preview
                        this.updatePreview($form, formData);
                        
                        // Hide edit form
                        setTimeout(() => {
                            $form.closest('.afaq-edit-form').slideUp(300);
                            $form.closest('.afaq-item-admin').find('.afaq-answer-preview').slideDown(300);
                            $form.closest('.afaq-item-admin').find('.afaq-edit-btn').text('تعديل').removeClass('active');
                        }, 1000);
                    } else {
                        this.showMessage('حدث خطأ: ' + response.data, 'error');
                    }
                })
                .fail(() => {
                    this.showMessage('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.', 'error');
                })
                .always(() => {
                    // Reset button state
                    $submitBtn.text(originalText).prop('disabled', false);
                    $form.removeClass('afaq-loading');
                });
        }
        
        deleteFAQ(event) {
            event.preventDefault();
            
            const $btn = $(event.currentTarget);
            const faqId = $btn.data('id');
            const $faqItem = $btn.closest('.afaq-item-admin');
            const questionText = $faqItem.find('.afaq-question-title').text();
            
            // Confirm deletion
            if (!confirm(`هل أنت متأكد من حذف السؤال: "${questionText}"؟\n\nهذا الإجراء لا يمكن التراجع عنه.`)) {
                return;
            }
            
            // Show loading state
            $btn.text('جاري الحذف...').prop('disabled', true);
            $faqItem.addClass('afaq-loading');
            
            const formData = {
                action: 'afaq_delete_faq',
                nonce: afaq_ajax.nonce,
                id: faqId
            };
            
            $.post(afaq_ajax.ajax_url, formData)
                .done((response) => {
                    if (response.success) {
                        this.showMessage('تم حذف السؤال بنجاح!', 'success');
                        
                        // Animate removal
                        $faqItem.fadeOut(500, function() {
                            $(this).remove();
                            
                            // Check if no FAQs left
                            if ($('.afaq-item-admin').length === 0) {
                                $('.afaq-items-container').html(
                                    '<div class="afaq-empty-state">' +
                                    '<h3>لا توجد أسئلة شائعة</h3>' +
                                    '<p>قم بإضافة السؤال الأول باستخدام النموذج أعلاه!</p>' +
                                    '</div>'
                                );
                            }
                        });
                    } else {
                        this.showMessage('حدث خطأ: ' + response.data, 'error');
                        $btn.text('حذف').prop('disabled', false);
                        $faqItem.removeClass('afaq-loading');
                    }
                })
                .fail(() => {
                    this.showMessage('حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.', 'error');
                    $btn.text('حذف').prop('disabled', false);
                    $faqItem.removeClass('afaq-loading');
                });
        }
        
        validateForm(event) {
            const $input = $(event.currentTarget);
            const $form = $input.closest('form');
            
            // Remove previous error styling
            $input.removeClass('error');
            
            // Validate based on input type
            if ($input.attr('required') && !$input.val().trim()) {
                $input.addClass('error');
                return false;
            }
            
            // Specific validations
            if ($input.attr('name') === 'question' || $input.attr('name') === 'edit_question') {
                if ($input.val().trim().length < 5) {
                    $input.addClass('error');
                    return false;
                }
            }
            
            if ($input.attr('name') === 'answer' || $input.attr('name') === 'edit_answer') {
                if ($input.val().trim().length < 10) {
                    $input.addClass('error');
                    return false;
                }
            }
            
            return true;
        }
        
        validateUpdateForm($form) {
            let isValid = true;
            
            const question = $form.find('input[name="edit_question"]').val().trim();
            const answer = $form.find('textarea[name="edit_answer"]').val().trim();
            
            if (question.length < 5) {
                $form.find('input[name="edit_question"]').addClass('error');
                this.showMessage('السؤال يجب أن يكون 5 أحرف على الأقل', 'error');
                isValid = false;
            }
            
            if (answer.length < 10) {
                $form.find('textarea[name="edit_answer"]').addClass('error');
                this.showMessage('الإجابة يجب أن تكون 10 أحرف على الأقل', 'error');
                isValid = false;
            }
            
            return isValid;
        }
        
        updatePreview($form, data) {
            const $item = $form.closest('.afaq-item-admin');
            
            // Update question title
            $item.find('.afaq-question-title').text(data.question);
            
            // Update answer preview
            const answerPreview = this.truncateText(data.answer.replace(/<[^>]*>/g, ''), 20);
            $item.find('.afaq-answer-preview').text(answerPreview);
            
            // Update sort order
            $item.find('.afaq-sort-order').text('الترتيب: ' + data.sort_order);
        }
        
        resetForm($editForm) {
            const $form = $editForm.find('form');
            
            // Reset to original values (stored in data attributes or hidden inputs)
            $form.find('input, textarea').each(function() {
                const $input = $(this);
                const originalValue = $input.data('original-value');
                if (originalValue !== undefined) {
                    $input.val(originalValue);
                }
            });
        }
        
        autoSave($input) {
            // Auto-save functionality for drafts
            const $form = $input.closest('.afaq-update-form');
            const faqId = $form.find('input[name="faq_id"]').val();
            
            // Save to localStorage as draft
            const draftData = {
                question: $form.find('input[name="edit_question"]').val(),
                answer: $form.find('textarea[name="edit_answer"]').val(),
                sort_order: $form.find('input[name="edit_sort_order"]').val(),
                timestamp: Date.now()
            };
            
            localStorage.setItem('afaq_draft_' + faqId, JSON.stringify(draftData));
        }
        
        initSortable() {
            // Initialize drag and drop sorting (if sortable library is available)
            if (typeof Sortable !== 'undefined' && $('.afaq-items-container').length) {
                new Sortable($('.afaq-items-container')[0], {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    onEnd: this.updateSortOrder.bind(this)
                });
            }
        }
        
        updateSortOrder(event) {
            const $items = $('.afaq-item-admin');
            const sortData = [];
            
            $items.each(function(index) {
                const faqId = $(this).data('id');
                sortData.push({
                    id: faqId,
                    sort_order: index
                });
            });
            
            // Send sort order to server
            $.post(afaq_ajax.ajax_url, {
                action: 'afaq_update_sort_order',
                nonce: afaq_ajax.nonce,
                sort_data: sortData
            });
        }
        
        initTooltips() {
            // Initialize tooltips for help text
            $(document).on('mouseenter', '.afaq-tooltip', function() {
                const $this = $(this);
                const tooltipText = $this.data('tooltip');
                
                if (tooltipText && !$this.find('.tooltip-content').length) {
                    $this.append('<div class="tooltip-content">' + tooltipText + '</div>');
                }
            });
            
            $(document).on('mouseleave', '.afaq-tooltip', function() {
                $(this).find('.tooltip-content').remove();
            });
        }
        
        showMessage(message, type = 'success') {
            // Remove existing messages
            $('.afaq-message').remove();
            
            // Create new message
            const $message = $('<div class="afaq-message ' + type + '">' + message + '</div>');
            
            // Insert message
            if ($('.afaq-admin-container').length) {
                $('.afaq-admin-container').prepend($message);
            } else {
                $('.wrap').prepend($message);
            }
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $message.offset().top - 100
            }, 300);
        }
        
        truncateText(text, wordLimit) {
            const words = text.split(' ');
            if (words.length > wordLimit) {
                return words.slice(0, wordLimit).join(' ') + '...';
            }
            return text;
        }
        
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.afaq-admin-container').length || $('.afaq-form-section').length) {
            new AFAQAdmin();
        }
        
        // Enhanced WordPress editor integration
        if (typeof wp !== 'undefined' && wp.editor) {
            // Initialize WordPress editor for answer fields
            $('.afaq-answer-editor').each(function() {
                const editorId = $(this).attr('id');
                wp.editor.initialize(editorId, {
                    tinymce: {
                        wpautop: true,
                        plugins: 'lists link textcolor',
                        toolbar1: 'bold italic underline | bullist numlist | link unlink',
                        setup: function(editor) {
                            editor.on('change', function() {
                                editor.save();
                            });
                        }
                    },
                    quicktags: true
                });
            });
        }
        
        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl+S to save (prevent default browser save)
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                const $activeForm = $('.afaq-update-form:visible');
                if ($activeForm.length) {
                    $activeForm.submit();
                }
            }
            
            // Escape to cancel edit
            if (e.key === 'Escape') {
                $('.afaq-cancel-edit:visible').click();
            }
        });
        
        // Warn about unsaved changes
        let hasUnsavedChanges = false;
        
        $(document).on('input', '.afaq-update-form input, .afaq-update-form textarea', function() {
            hasUnsavedChanges = true;
        });
        
        $(document).on('submit', '.afaq-update-form', function() {
            hasUnsavedChanges = false;
        });
        
        $(window).on('beforeunload', function() {
            if (hasUnsavedChanges) {
                return 'لديك تغييرات غير محفوظة. هل أنت متأكد من مغادرة الصفحة؟';
            }
        });
    });
    
})(jQuery);