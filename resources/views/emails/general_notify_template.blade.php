<!DOCTYPE html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <title>{{ $title ?? 'رسالة من سوقنا' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <style>
      body {
        margin: 0;
        padding: 0;
        font-family: "Tajawal", sans-serif;
        background-color: #f7f9fc;
        direction: rtl;
        text-align: right;
      }
      .email-container {
        max-width: 600px;
        margin: 40px auto;
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      }
      .email-header {
        background-color: #dae8fc;
        padding: 10px 24px;
        text-align: center;
      }
      .email-header img {
        max-width: 150px;
        height: auto;
      }
      .email-body {
        padding: 30px;
        color: #333333;
      }
      .email-footer {
        text-align: center;
        font-size: 13px;
        color: #a0a0a0;
        padding: 20px;
        background-color: #f0f4f9;
        border-top: 1px solid #e0e4e8;
      }
      .email-footer a {
        color: #1a73e8;
        margin: 0 5px;
        text-decoration: none;
      }
      @media (max-width: 600px) {
        .email-container {
          margin: 20px auto;
          border-radius: 0;
        }
        .email-body {
          padding: 20px;
        }
      }
    </style>
  </head>

  <body>
    <div class="email-container">
      <div class="email-header">
        <img src="https://api.souqna-sa.com/storage/images/logo.png" alt="شعار سوقنا" />
      </div>
      <div class="email-body">
        {{-- HERE WE INJECT USER CONTENT --}}
        {!! $bodyContent !!}
      </div>
      <div class="email-footer">
        <p>© 2025 سوقنا. جميع الحقوق محفوظة.</p>
      </div>
    </div>
  </body>
</html>
