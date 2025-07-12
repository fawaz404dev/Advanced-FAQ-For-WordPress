# المساهمة في Advanced FAQ for WordPress

شكراً لاهتمامك بالمساهمة في تطوير إضافة الأسئلة الشائعة المتقدمة! نرحب بجميع أنواع المساهمات.

## 🚀 كيفية البدء

### متطلبات التطوير
- WordPress 5.0+
- PHP 7.4+
- Node.js 14+ (للأدوات التطويرية)
- Composer (لإدارة التبعيات)

### إعداد بيئة التطوير

1. **استنساخ المشروع**
   ```bash
   git clone https://github.com/fjomah/advanced-faq-wordpress.git
   cd advanced-faq-wordpress
   ```

2. **تثبيت التبعيات**
   ```bash
   composer install
   npm install
   ```

3. **إعداد WordPress المحلي**
   - ضع المجلد في `/wp-content/plugins/`
   - فعّل الإضافة من لوحة التحكم

## 📝 أنواع المساهمات

### 🐛 الإبلاغ عن الأخطاء

قبل الإبلاغ عن خطأ، تأكد من:
- البحث في Issues الموجودة
- التأكد من استخدام أحدث إصدار
- اختبار الخطأ مع قالب WordPress الافتراضي

**معلومات مطلوبة:**
- إصدار WordPress
- إصدار PHP
- إصدار الإضافة
- وصف مفصل للمشكلة
- خطوات إعادة إنتاج الخطأ
- لقطات شاشة (إن أمكن)

### 💡 اقتراح ميزات جديدة

1. تحقق من [خارطة الطريق](ROADMAP.md)
2. ابحث في Issues الموجودة
3. أنشئ Issue جديد مع:
   - وصف واضح للميزة
   - حالات الاستخدام
   - الفائدة المتوقعة
   - أمثلة أو مخططات

### 🔧 المساهمة بالكود

#### معايير الكود

**PHP:**
- اتبع [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- استخدم PSR-4 للـ autoloading
- أضف PHPDoc للوظائف والكلاسات
- استخدم type hints عند الإمكان

**JavaScript:**
- استخدم ES6+ features
- اتبع [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- أضف JSDoc للوظائف

**CSS:**
- استخدم BEM methodology
- اتبع [WordPress CSS Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/css/)
- استخدم CSS custom properties للمتغيرات

#### عملية التطوير

1. **Fork المشروع**
2. **أنشئ branch جديد**
   ```bash
   git checkout -b feature/feature-name
   # أو
   git checkout -b bugfix/bug-description
   ```

3. **اكتب الكود**
   - اتبع معايير الكود
   - أضف تعليقات واضحة
   - اختبر التغييرات

4. **اكتب الاختبارات**
   ```bash
   # تشغيل الاختبارات
   composer test
   
   # فحص جودة الكود
   composer lint
   ```

5. **Commit التغييرات**
   ```bash
   git add .
   git commit -m "feat: add new feature description"
   ```

6. **Push وإنشاء Pull Request**
   ```bash
   git push origin feature/feature-name
   ```

#### رسائل Commit

استخدم [Conventional Commits](https://www.conventionalcommits.org/):

- `feat:` ميزة جديدة
- `fix:` إصلاح خطأ
- `docs:` تحديث الوثائق
- `style:` تغييرات التنسيق
- `refactor:` إعادة هيكلة الكود
- `test:` إضافة اختبارات
- `chore:` مهام صيانة

**أمثلة:**
```
feat: add custom FAQ support for individual posts
fix: resolve toggle animation issue on mobile devices
docs: update installation instructions
style: improve CSS for RTL languages
```

## 🧪 الاختبارات

### تشغيل الاختبارات

```bash
# جميع الاختبارات
composer test

# اختبارات PHP فقط
composer test:php

# اختبارات JavaScript فقط
npm test

# فحص جودة الكود
composer lint
npm run lint
```

### كتابة اختبارات جديدة

- ضع اختبارات PHP في `/tests/php/`
- ضع اختبارات JavaScript في `/tests/js/`
- اتبع نمط التسمية: `test-feature-name.php`

## 📚 الوثائق

### تحديث الوثائق

- حدّث `README.md` للميزات الجديدة
- أضف أمثلة للاستخدام
- حدّث `CHANGELOG.md`
- أضف PHPDoc و JSDoc

### كتابة الوثائق

- استخدم لغة واضحة ومباشرة
- أضف أمثلة عملية
- استخدم لقطات الشاشة عند الحاجة
- اكتب بالعربية للمستخدمين النهائيين
- اكتب بالإنجليزية للمطورين

## 🔍 مراجعة الكود

### معايير المراجعة

- **الوظائف**: هل الكود يعمل كما هو متوقع؟
- **الأداء**: هل يؤثر على سرعة الموقع؟
- **الأمان**: هل يتبع معايير الأمان؟
- **التوافق**: هل يعمل مع إصدارات WordPress المدعومة؟
- **الوصولية**: هل يدعم معايير الوصولية؟

### عملية المراجعة

1. مراجعة تلقائية (CI/CD)
2. مراجعة الكود من المطورين
3. اختبار الوظائف
4. مراجعة الوثائق
5. الموافقة والدمج

## 🏷️ الإصدارات

نتبع [Semantic Versioning](https://semver.org/):

- **MAJOR**: تغييرات غير متوافقة مع الإصدارات السابقة
- **MINOR**: ميزات جديدة متوافقة
- **PATCH**: إصلاحات الأخطاء

## 📞 التواصل

- **GitHub Issues**: للأخطاء والاقتراحات
- **GitHub Discussions**: للنقاشات العامة
- **البريد الإلكتروني**: info@fjomah.com
- **واتساب**: +201111933193

## 📋 قائمة المراجعة للـ Pull Request

- [ ] الكود يتبع معايير المشروع
- [ ] جميع الاختبارات تمر بنجاح
- [ ] الوثائق محدثة
- [ ] رسالة commit واضحة
- [ ] لا توجد تعارضات في الدمج
- [ ] تم اختبار الميزة/الإصلاح
- [ ] متوافق مع إصدارات WordPress المدعومة

## 🎉 شكر وتقدير

شكراً لجميع المساهمين في هذا المشروع! مساهماتكم تجعل هذه الإضافة أفضل للجميع.

---

**للأسئلة أو المساعدة، لا تتردد في التواصل معنا!**