<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تجربة تسجيل ناشر جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 40px;
            font-family: Tahoma, Arial, sans-serif;
        }

        .card {
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #responseArea {
            display: none;
            margin-top: 20px;
        }

        pre {
            direction: ltr;
            text-align: left;
            background: #2d2d2d;
            color: #4af626;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="card p-4">
            <h3 class="text-center mb-4">تسجيل ناشر جديد (اختبار API)</h3>

            <form id="registerForm">
                <div class="mb-3">
                    <label>الاسم</label>
                    <input type="text" id="name" class="form-control" value="منور الوائلي" required>
                </div>

                <div class="mb-3">
                    <label>البريد الإلكتروني</label>
                    <input type="email" id="email" class="form-control" value="munawar712326@gmail.com" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>كلمة المرور</label>
                        <input type="password" id="password" class="form-control" value="StrongPassword123!" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>تأكيد كلمة المرور</label>
                        <input type="password" id="password_confirmation" class="form-control"
                            value="StrongPassword123!" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>رقم الهاتف</label>
                        <input type="text" id="phone" class="form-control" value="+967770000000">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>الشركة</label>
                        <input type="text" id="company" class="form-control" value="دار المعرفة للطباعة والنشر">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>الدولة</label>
                        <input type="text" id="country" class="form-control" value="اليمن">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="submitBtn">إرسال الطلب</button>
            </form>

            <div id="responseArea">
                <hr>
                <h5>الرد من السيرفر (Response):</h5>
                <pre id="jsonResponse"></pre>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            e.preventDefault(); // منع إعادة تحميل الصفحة

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerText = "جاري الإرسال...";

            // 1. تجميع البيانات في كائن JSON
            const payload = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value,
                phone: document.getElementById('phone').value,
                company: document.getElementById('company').value,
                country: document.getElementById('country').value
            };

            // 2. إرسال الطلب عبر Fetch API
            fetch('/api/publisher/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    // 3. عرض النتيجة
                    document.getElementById('responseArea').style.display = 'block';
                    // تحويل الرد إلى شكل مقروء وجميل
                    document.getElementById('jsonResponse').textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    document.getElementById('responseArea').style.display = 'block';
                    document.getElementById('jsonResponse').textContent = "حدث خطأ في الاتصال: \n" + error;
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = "إرسال الطلب";
                });
        });
    </script>

</body>

</html>
