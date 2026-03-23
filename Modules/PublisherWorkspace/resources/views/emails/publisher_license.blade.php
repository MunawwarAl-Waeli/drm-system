<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }

        .warning {
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>إصدار رخصة ناشر جديدة</h2>
        </div>

        <div class="content">
            <p>مرحباً <strong>{{ $publisher->name }}</strong>،</p>
            <p>يسعدنا انضمامك إلى منصتنا لحماية وتشفير المحتوى الرقمي.</p>

            <p>لقد تم إنشاء حسابك بنجاح. تجد في <strong>المرفقات</strong> ملف الرخصة الخاص بك بصيغة
                (<code>.lzpk</code>).</p>

            <h3>خطوات البدء:</h3>
            <ol>
                <li>قم بتحميل برنامج (Writer) الخاص بنا من موقعنا.</li>
                <li>قم بتحميل الملف المرفق في هذا الإيميل إلى جهازك.</li>
                <li>افتح برنامج (Writer) واستورد ملف الرخصة لتبدأ بتشفير ملفاتك.</li>
            </ol>

            <p class="warning">⚠️ تنبيه أمني: هذا الملف يحتوي على مفاتيح التشفير الخاصة بك. لا تقم بمشاركته مع أي شخص
                آخر تحت أي ظرف.</p>
        </div>

        <div class="footer">
            <p>مع تحيات،<br>فريق الدعم الفني - نظام الحماية</p>
        </div>
    </div>
</body>

</html>
