<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Product Notification</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 8px;">
        <h2>Hello {{ $user->name }},</h2>

        <p>We're excited to let you know that a new product has just been added:</p>

        <h3>{{ $product->name }}</h3>
        <p>{{ $product->description }}</p>

        @if($product->price)
        <p><strong>Price:</strong> ${{ number_format($product->price, 2) }}</p>
        @endif

        <a href="{{ url('/products/' . $product->id) }}" style="display: inline-block; padding: 10px 15px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">
            View Product
        </a>

        <p style="margin-top: 30px;">Thanks,<br>The Team</p>
    </div>
</body>
</html>
