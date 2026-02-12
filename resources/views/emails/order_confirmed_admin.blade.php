<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>New Order Received</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #2c3e50;
        }
        p {
            font-size: 16px;
            margin-top: 15px;
        }
        .btn {
            display: inline-block;
            margin-top: 25px;
            background-color: #28a745;
            color: white !important;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>New Order Received</h1>
        <p>A new order with ID <strong>#{{ $order_id }}</strong> has been placed.</p>
        <a href="{{ url('/admin/orders/' . $order_id) }}" class="btn">View Order</a>
        <div class="footer">
            This is an automated message. Please do not reply.
        </div>
    </div>
</body>
</html>
