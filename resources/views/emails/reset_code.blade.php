<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body { font-family: 'Tahoma', sans-serif; background-color: #f9f9f9; padding: 40px; text-align: center; }
        .card {
            background: white;
            padding: 25px 30px;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .code {
            font-size: 28px;
            font-weight: bold;
            color: #3490dc;
            margin-top: 20px;
            direction: ltr;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>إعادة تعيين كلمة المرور</h2>
        <p>رمز التحقق الخاص بك هو:</p>
        <div class="code">{{ $code }}</div>
        <p style="margin-top: 20px;">هذا الرمز صالح لمدة 15 دقيقة.</p>
    </div>
</body>
</html>
