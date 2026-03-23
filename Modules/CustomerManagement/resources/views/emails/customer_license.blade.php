<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>تفاصيل رخصة الاستخدام</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 25px;
        }

        .welcome {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .instructions {
            background-color: #f1f8ff;
            border-right: 4px solid #3498db;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .instructions h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 16px;
        }

        ol {
            margin-bottom: 0;
            padding-right: 20px;
        }

        ol li {
            margin-bottom: 8px;
        }

        .footer {
            background-color: #f4f4f4;
            color: #777777;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
        }

        .note {
            display: inline-block;
            margin-top: 20px;
            padding: 10px;
            background-color: #fff9e6;
            border: 1px dashed #f1c40f;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>نظام حماية المحتوى</h2>
        </div>

        <div class="content">
            <div class="welcome">
                مرحباً {{ $license->name }}،
                @if($license->company)
                    <br><span style="font-size: 14px; color: #666;">({{ $license->company }})</span>
                @endif
            </div>

            <p>لقد تم إصدار رخصة استخدام جديدة خاصة بك بنجاح.</p>

            @if($license->type === 'individual')
                <div class="instructions">
                    <h3>كيفية تفعيل رخصتك الفردية:</h3>
                    <p>لقد أرفقنا لك ملف التفعيل الخاص بك في هذه الرسالة.</p>
                    <ol>
                        <li>تأكد من تحميل وتثبيت <strong>برنامج القارئ (Reader App)</strong> على جهازك.</li>
                        <li>قم بتحميل الملف المرفق في هذا الإيميل إلى جهازك.</li>
                        <li>انقر نقراً مزدوجاً على الملف لفتحه، وسيتم تفعيل اشتراكك وربط جهازك تلقائياً.</li>
                    </ol>
                </div>
                <div class="note">
                    <strong>تنبيه أمني:</strong> هذا الملف مخصص للاستخدام الشخصي ويرتبط بجهازك فور تفعيله. يرجى عدم مشاركته
                    مع الآخرين.
                </div>

            @elseif($license->type === 'group')
                <div class="instructions">
                    <h3>تفاصيل كروت التفعيل (Vouchers):</h3>
                    <p>لقد أرفقنا لك ملف <strong>CSV</strong> يحتوي على كروت التفعيل (PIN Codes) المطلوبة.</p>
                    <p>يمكنك توزيع هذه الكروت على الطلاب أو المستفيدين. لتفعيل الكرت، يجب على المستفيد إتباع الآتي:</p>
                    <ol>
                        <li>تحميل وفتح <strong>برنامج القارئ (Reader App)</strong>.</li>
                        <li>إنشاء حساب جديد أو تسجيل الدخول.</li>
                        <li>إدخال كود الكرت (PIN Code) في خانة التفعيل داخل البرنامج للوصول للمحتوى.</li>
                    </ol>
                </div>
            @endif

            @if($license->note)
                <p style="margin-top: 25px;"><strong>ملاحظات إضافية:</strong> {{ $license->note }}</p>
            @endif

        </div>

        <div class="footer">
            <p>هذه رسالة تلقائية من النظام، يرجى عدم الرد عليها.</p>
            <p>&copy; {{ date('Y') }} جميع الحقوق محفوظة.</p>
        </div>
    </div>

</body>

</html>
