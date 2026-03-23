-- 🌟 الجدول الجديد: الناشرون
CREATE TABLE LocalPublishers (
    PublisherId INTEGER PRIMARY KEY,
    Name TEXT,          -- اسم الناشر (مثلاً: جامعة العلوم)
    SupportEmail TEXT,  -- إيميل الدعم الفني (لكي يتواصل معه الطالب لو تعطلت رخصته)
    LogoUrl TEXT        -- (اختياري) مسار شعار الناشر لعرضه في الواجهة
);

-- تعديل جدول الرخص ليرتبط بالناشر
CREATE TABLE LocalLicenses (
    LicenseId INTEGER PRIMARY KEY,
    PublisherId INTEGER, -- 👈 هنا الربط العبقري!
    CustomerName TEXT,
    CustomerEmail TEXT,
    ValidUntil DATETIME NULL,
    ActivatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(PublisherId) REFERENCES LocalPublishers(PublisherId)
);

-- 2. جدول المنشورات (الحزم)
CREATE TABLE LocalPublications (
    PublicationId INTEGER PRIMARY KEY,
    LicenseId INTEGER,
    Name TEXT,
    AccessMode TEXT,
    ValidUntil DATETIME NULL,
    FOREIGN KEY(LicenseId) REFERENCES LocalLicenses(LicenseId)
);

-- 3. جدول الملفات الأساسية
CREATE TABLE LocalDocuments (
    DocumentUUID TEXT PRIMARY KEY,
    LicenseId INTEGER,
    PublicationId INTEGER NULL, -- قد يكون Null إذا كان الملف فردياً
    Title TEXT,
    EncryptedKey TEXT, -- المفتاح المشفر لفك تشفير الـ PDF
    LocalFilePath TEXT, -- أين تم تحميل الملف المشفر على كمبيوتر الطالب
    IsDownloaded BOOLEAN DEFAULT 0,
    FOREIGN KEY(LicenseId) REFERENCES LocalLicenses(LicenseId),
    FOREIGN KEY(PublicationId) REFERENCES LocalPublications(PublicationId)
);

-- 4. جدول قواعد الحماية والتتبع (عصب النظام Offline)
CREATE TABLE DocumentSecurityRules (
    DocumentUUID TEXT PRIMARY KEY,

    -- [أ] إعدادات انتهاء الصلاحية
    ExpiryMode TEXT, -- (never, fixed_date, days_from_first_use)
    ExpiryFixedDate DATETIME NULL,
    ExpiryDays INTEGER NULL,
    FirstOpenedAt DATETIME NULL, -- (تتبع محلي): يسجل التطبيق التاريخ عند أول نقرة لفتح الملف

    -- [ب] إعدادات التحقق من الإنترنت
    VerifyMode TEXT, -- (never, each_time,only_when_internet, every_x_days, after_x_days_then_never)
    VerifyFrequencyDays INTEGER NULL,//عدد الايام
    VerifyGracePeriodDays INTEGER NULL,//فترة الصلاحيه
    LastServerSync DATETIME NULL, -- (تتبع محلي): يحدثه التطبيق كلما اتصل بالسيرفر بنجاح

    -- [ج] إعدادات الاستخدام ( والمشاهدة)
    MaxViews INTEGER NULL,
    CurrentViews INTEGER DEFAULT 0, -- (تتبع محلي): يزيده التطبيق +1 مع كل فتح

    FOREIGN KEY(DocumentUUID) REFERENCES LocalDocuments(DocumentUUID)
);



http://localhost:8000/api/writer/activate


