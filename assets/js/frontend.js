/**
 * Advanced FAQ for WordPress - Frontend JavaScript
 * Author: فوزي جمعة
 * Website: fjomah.com
 */

(function($) {
    'use strict';
    
    // FAQ Widget Class
    class AFAQWidget {
        constructor() {
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.setupAccessibility();
            this.handleInitialState();
        }
        
        bindEvents() {
            // Click event for questions
            $(document).on('click', '.afaq-question', this.toggleAnswer.bind(this));
            
            // Keyboard navigation
            $(document).on('keydown', '.afaq-question', this.handleKeyboard.bind(this));
            
            // Window resize handler
            $(window).on('resize', this.handleResize.bind(this));
        }
        
        toggleAnswer(event) {
            event.preventDefault();
            
            const $question = $(event.currentTarget);
            const $item = $question.closest('.afaq-item');
            const $answer = $item.find('.afaq-answer');
            const $toggle = $question.find('.afaq-toggle');
            
            // Get animation speed from settings or use default
            const animationSpeed = this.getAnimationSpeed();
            
            if ($answer.hasClass('show')) {
                // Close answer
                this.closeAnswer($question, $answer, $toggle, animationSpeed);
            } else {
                // Close other open answers (accordion behavior)
                this.closeAllAnswers();
                
                // Open current answer
                this.openAnswer($question, $answer, $toggle, animationSpeed);
            }
            
            // Track analytics if available
            this.trackEvent('faq_toggle', {
                question: $question.find('.afaq-question-text').text().trim(),
                action: $answer.hasClass('show') ? 'close' : 'open'
            });
        }
        
        openAnswer($question, $answer, $toggle, speed) {
            $question.addClass('active').attr('aria-expanded', 'true');
            
            if (speed > 0) {
                $answer.slideDown(speed, () => {
                    $answer.addClass('show');
                    this.scrollToQuestion($question);
                });
            } else {
                $answer.show().addClass('show');
                this.scrollToQuestion($question);
            }
        }
        
        closeAnswer($question, $answer, $toggle, speed) {
            $question.removeClass('active').attr('aria-expanded', 'false');
            
            if (speed > 0) {
                $answer.slideUp(speed, () => {
                    $answer.removeClass('show');
                });
            } else {
                $answer.hide().removeClass('show');
            }
        }
        
        closeAllAnswers() {
            const speed = this.getAnimationSpeed();
            
            $('.afaq-question.active').each((index, element) => {
                const $question = $(element);
                const $answer = $question.siblings('.afaq-answer');
                const $toggle = $question.find('.afaq-toggle');
                
                this.closeAnswer($question, $answer, $toggle, speed);
            });
        }
        
        handleKeyboard(event) {
            const $question = $(event.currentTarget);
            
            switch(event.key) {
                case 'Enter':
                case ' ': // Space
                    event.preventDefault();
                    $question.click();
                    break;
                    
                case 'ArrowDown':
                    event.preventDefault();
                    this.focusNext($question);
                    break;
                    
                case 'ArrowUp':
                    event.preventDefault();
                    this.focusPrevious($question);
                    break;
                    
                case 'Home':
                    event.preventDefault();
                    this.focusFirst();
                    break;
                    
                case 'End':
                    event.preventDefault();
                    this.focusLast();
                    break;
            }
        }
        
        focusNext($current) {
            const $questions = $('.afaq-question');
            const currentIndex = $questions.index($current);
            const nextIndex = (currentIndex + 1) % $questions.length;
            $questions.eq(nextIndex).focus();
        }
        
        focusPrevious($current) {
            const $questions = $('.afaq-question');
            const currentIndex = $questions.index($current);
            const prevIndex = currentIndex === 0 ? $questions.length - 1 : currentIndex - 1;
            $questions.eq(prevIndex).focus();
        }
        
        focusFirst() {
            $('.afaq-question').first().focus();
        }
        
        focusLast() {
            $('.afaq-question').last().focus();
        }
        
        setupAccessibility() {
            $('.afaq-question').each(function() {
                const $question = $(this);
                const $answer = $question.siblings('.afaq-answer');
                const questionId = 'afaq-q-' + Math.random().toString(36).substr(2, 9);
                const answerId = 'afaq-a-' + Math.random().toString(36).substr(2, 9);
                
                // Set ARIA attributes
                $question.attr({
                    'role': 'button',
                    'aria-expanded': 'false',
                    'aria-controls': answerId,
                    'id': questionId,
                    'tabindex': '0'
                });
                
                $answer.attr({
                    'role': 'region',
                    'aria-labelledby': questionId,
                    'id': answerId
                });
                
                // Wrap answer content
                if (!$answer.find('.afaq-answer-content').length) {
                    $answer.wrapInner('<div class="afaq-answer-content"></div>');
                }
            });
        }
        
        handleInitialState() {
            // Check if any answers should be expanded by default
            if (this.shouldExpandByDefault()) {
                $('.afaq-question').first().click();
            }
            
            // Handle URL hash for direct linking
            this.handleUrlHash();
        }
        
        shouldExpandByDefault() {
            // This can be configured via settings
            return false; // Default: collapsed
        }
        
        handleUrlHash() {
            const hash = window.location.hash;
            if (hash && hash.startsWith('#faq-')) {
                const questionIndex = parseInt(hash.replace('#faq-', '')) - 1;
                const $question = $('.afaq-question').eq(questionIndex);
                
                if ($question.length) {
                    setTimeout(() => {
                        $question.click();
                        this.scrollToQuestion($question);
                    }, 100);
                }
            }
        }
        
        scrollToQuestion($question) {
            const offset = $question.offset().top - 100; // 100px offset from top
            
            $('html, body').animate({
                scrollTop: offset
            }, 300);
        }
        
        getAnimationSpeed() {
            // Try to get speed from WordPress settings, fallback to 300ms
            return window.afaqSettings && window.afaqSettings.animationSpeed 
                ? parseInt(window.afaqSettings.animationSpeed) 
                : 300;
        }
        
        handleResize() {
            // Handle responsive adjustments if needed
            clearTimeout(this.resizeTimeout);
            this.resizeTimeout = setTimeout(() => {
                // Responsive logic here
            }, 250);
        }
        
        trackEvent(action, data) {
            // Google Analytics tracking if available
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'FAQ',
                    event_label: data.question,
                    custom_map: {
                        dimension1: data.action
                    }
                });
            }
            
            // Google Analytics Universal tracking if available
            if (typeof ga !== 'undefined') {
                ga('send', 'event', 'FAQ', action, data.question);
            }
        }
        
        // Public methods for external access
        openAll() {
            $('.afaq-question:not(.active)').click();
        }
        
        closeAll() {
            this.closeAllAnswers();
        }
        
        openQuestion(index) {
            const $question = $('.afaq-question').eq(index);
            if ($question.length && !$question.hasClass('active')) {
                $question.click();
            }
        }
        
        getOpenQuestions() {
            return $('.afaq-question.active').map(function() {
                return $(this).find('.afaq-question-text').text().trim();
            }).get();
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.afaq-widget').length) {
            window.afaqWidget = new AFAQWidget();
            
            // Add question numbers if enabled
            if (window.afaqSettings && window.afaqSettings.showQuestionNumbers) {
                $('.afaq-question-text').each(function(index) {
                    $(this).prepend('<span class="afaq-question-number">' + (index + 1) + '. </span>');
                });
            }
            
            // Apply custom CSS if provided
            if (window.afaqSettings && window.afaqSettings.customCSS) {
                $('<style>').text(window.afaqSettings.customCSS).appendTo('head');
            }
        }
    });
    
    // Expose global functions for external use
    window.AFAQ = {
        openAll: function() {
            if (window.afaqWidget) {
                window.afaqWidget.openAll();
            }
        },
        closeAll: function() {
            if (window.afaqWidget) {
                window.afaqWidget.closeAll();
            }
        },
        openQuestion: function(index) {
            if (window.afaqWidget) {
                window.afaqWidget.openQuestion(index);
            }
        },
        getOpenQuestions: function() {
            return window.afaqWidget ? window.afaqWidget.getOpenQuestions() : [];
        }
    };
    
})(jQuery);