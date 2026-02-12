<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShippingGateController;
use App\Http\Controllers\SocialMediaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiscountCouponController;
use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\ReturnsPolicyController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\StoreInfoController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\OfferBannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IframeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentGateController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SystemNotificationController;
use App\Http\Controllers\TrackingLinkController;
use App\Http\Controllers\ImportController;

Route::post('/import-stores', [ImportController::class, 'importStores']);



//System Notfication  -> by Admin Dashbaord
Route::post('notification', [SystemNotificationController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
  Route::get('admin/notification', [SystemNotificationController::class, 'index']);
  Route::get('notification/{notification}', [SystemNotificationController::class, 'show']);
  Route::put('notification/{notification}', [SystemNotificationController::class, 'update']);
  Route::patch('notification/{notification}', [SystemNotificationController::class, 'update']);
  Route::delete('notification/{notification}', [SystemNotificationController::class, 'destroy']);
});



//Cart Routes 

Route::middleware('auth:sanctum')->group(function () {
  Route::get('/cart', [CartController::class, 'index']);
  Route::get('/admin_cart', [CartController::class, 'indexAdmin']);
  Route::post('/cart', [CartController::class, 'addItem']);
  Route::delete('/cart', [CartController::class, 'clear']);
  Route::delete('/cart/{product_id}', [CartController::class, 'clearOne']);
  Route::post('/calculate/shiping', [CartController::class, 'CalculateShipping']);
});


//notifications
Route::middleware('auth:sanctum')->group(function () {
  Route::get('notifications', [AuthController::class, 'GetNotifications']);
  Route::delete('notifications/{id}', [AuthController::class, 'DeleteNotification']);
  Route::delete('notifications', [AuthController::class, 'DeleteNotifications']);
  Route::post('/notifications/{id}/read', [AuthController::class, 'ReadNotification']);
  Route::post('/notifications/read', [AuthController::class, 'ReadNotifications']);
});

//Payment Routes
Route::get('/admin/payment/status', [PaymentSettingsController::class, 'status']);
Route::post('/admin/payment/enable', [PaymentSettingsController::class, 'enablePayment']);
Route::post('/admin/payment/disable', [PaymentSettingsController::class, 'disablePayment']);


Route::middleware('auth:sanctum')->group(function () {
  Route::get('/admin/payments', [PaymentSettingsController::class, 'GetPayments']);
  Route::get('/admin/orders/states', [PaymentSettingsController::class, 'GetOrdersStates']);
  Route::get('/admin/shippments', [PaymentSettingsController::class, 'GetShippments']);
});


//Email Marketing Service

Route::prefix('/admin/email-marketing')->group(function () {
  Route::get('/status', [ServiceController::class, 'EmailMarkettingStatus']);
  Route::post('/enable', [ServiceController::class, 'enableMailMarketing']);
  Route::post('/disable', [ServiceController::class, 'disableMailMarketing']);
  Route::middleware('auth:sanctum')->post('/set-up' ,  [ServiceController::class, 'SetupEmail']);
});



//Append Store Visit
Route::post('/append_visit', [ServiceController::class, 'AppendUserVisit']);
Route::get('/getStats', [ServiceController::class, 'getStats']);




Route::middleware('auth:sanctum')->post('users/notify', [ServiceController::class, 'SendUsersNotifcation']);


Route::middleware('auth:sanctum')->group(function () {

  //Manage Shipping Gates
  Route::get('/shipping-gates', [ShippingGateController::class, 'index']);
  Route::post('/shipping-gates/enable', [ShippingGateController::class, 'storeOrUpdate']);
  Route::post('/shipping-gates/admin/update', [ShippingGateController::class, 'UpdateGateByAdmin']);
  Route::delete('/shipping-gates/{name}', [ShippingGateController::class, 'DeleteGate']);




  //Manage Payments Gates
  Route::get('/payment-gates', [PaymentGateController::class, 'index']);
  Route::post('/payment-gates/store-or-update', [PaymentGateController::class, 'storeOrUpdate']);

  Route::post('/payment-gateway/update', [PaymentSettingsController::class, 'UpdatePaymentGate']);
});



// Auth routes

Route::post('/password-reset', [AuthController::class, 'SendCode']);
Route::post('/password-reset-confirm', [AuthController::class, 'PasswordResetConfirm']);
Route::post('/password-reset-update', [AuthController::class, 'PasswordResetUpdate']);

Route::middleware('auth:sanctum')->post('/update-profile', [AuthController::class, 'updateProfile']);


Route::post('/test-mail', [AuthController::class, 'SendTestMail']);

Route::post('login', [AuthController::class, 'login']);
Route::post('login-w-google', [AuthController::class, 'loginWithgoogle']);
Route::post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Public (index, show only)
Route::apiResource('discount-coupons', DiscountCouponController::class)->only(['index', 'show']);
Route::apiResource('about-us', AboutUsController::class)->only(['index', 'show']);
Route::apiResource('returns-policy', ReturnsPolicyController::class)->only(['index', 'show']);
Route::apiResource('privacy-policy', PrivacyPolicyController::class)->only(['index', 'show']);
Route::apiResource('store-info', StoreInfoController::class)->only(['index', 'show']);
Route::apiResource('contact-us', ContactUsController::class)->only(['index', 'store', 'show']);
Route::apiResource('banners', BannerController::class)->only(['index', 'show']);
Route::apiResource('blogs', BlogController::class)->only(['index', 'show']);
Route::apiResource('offer-banners', OfferBannerController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('brands', BrandController::class)->only(['index', 'show']);
Route::apiResource('testimonials', TestimonialController::class)->only(['index', 'show']);

Route::get('/products/by-category', [ProductController::class, 'getByCategory']);
Route::get('/products/by-brand', [ProductController::class, 'getByBrand']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::apiResource('product-reviews', ProductReviewController::class)->only(['index', 'show', 'store']);
Route::get('/recommended_products', [ProductController::class, 'GetRecommendedProducts']);
Route::get('/top_products', [ProductController::class, 'GetTopProducts']);
Route::get('/has_sale', [ProductController::class, 'GetSaleProducts']);




Route::get('coupon/by_name/{name}', [DiscountCouponController::class, 'showByname']);
Route::get('/tracking/{tracking_link_id}', [TrackingLinkController::class, 'track']);


  Route::post('privacy-policy', [PrivacyPolicyController::class, 'updateOrCreate']);

Route::middleware('auth:sanctum')->group(function () {
  Route::get('order/latest', [OrderController::class, 'GetLastOrder']);
  Route::get('/customers', [CustomerController::class, 'GetCustomers']);
  Route::apiResource('discount-coupons', DiscountCouponController::class)->except(['index', 'show']);
  
  Route::post('about-us', [AboutUsController::class, 'update']);
Route::delete('about-us/{id}', [AboutUsController::class, 'destroy']);
  Route::put('returns-policy', [ReturnsPolicyController::class, 'update']);
 

  Route::apiResource('store-info', StoreInfoController::class)->except(['index', 'show']);

  //Store Stats Route
  Route::get('/store-stats', [StoreInfoController::class, 'StoreStats']);


  Route::apiResource('contact-us', ContactUsController::class)->except(['index', 'store', 'show']);
  Route::apiResource('banners', BannerController::class)->except(['index', 'show']);
  Route::apiResource('blogs', BlogController::class)->except(['index', 'show']);
  Route::apiResource('offer-banners', OfferBannerController::class)->except(['index', 'show']);
  Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
  //Category Stats
  Route::get('categories/{id}/states', [CategoryController::class, 'CategoryStats']);
  Route::apiResource('brands', BrandController::class)->except(['index', 'show']);
  //brand Stats
  Route::get('brands/{id}/states', [BrandController::class, 'BrandStats']);
  Route::apiResource('testimonials', TestimonialController::class)->except(['index', 'show', 'update']);

  Route::post('testimonials/{id}', [TestimonialController::class, 'update']);
  Route::apiResource('products', ProductController::class)->except(['index', 'show']);


  Route::apiResource('shipping-gates', ShippingGateController::class)->except(['update']);
  Route::post('shipping-gates/update/{id}', [ShippingGateController::class, 'update']);
  Route::post('products/facebook/publish', [ProductController::class, 'PublishProductsTofacebook']);
  Route::post('products/instagram/publish', [ProductController::class, 'PublishProductsToInstaGram']);
  Route::delete('product-reviews/{id}', [ProductReviewController::class, 'destroy']);
  Route::post('store-info', [StoreInfoController::class, 'update']);
  Route::post('banners/{id}', [BannerController::class, 'update']);
  Route::post('blogs/{id}', [BlogController::class, 'update']);
  Route::post('brands/{id}', [BrandController::class, 'update']);
  Route::post('/iframes', [IframeController::class, 'update']);
  Route::post('/products/update/{id}', [ProductController::class, 'update']);
  Route::get('/products/stats/{id}', [ProductController::class, 'ProductStats']);
  Route::post('/offer-banners/update/{id}', [OfferBannerController::class, 'update']);
  Route::post('/categories/update/{id}', [CategoryController::class, 'update']);
  Route::apiResource('coordinators', CoordinatorController::class);
  Route::apiResource('tracking_links', TrackingLinkController::class)->only(['store', 'index', 'show', 'destroy', 'update']);
  Route::put('/archive/{tracking_link_id}', [TrackingLinkController::class, 'archive']);
  Route::post('/archive/{tracking_link_id}', [TrackingLinkController::class, 'archive']);
  Route::post('/unarchive/{tracking_link_id}', [TrackingLinkController::class, 'unarchive']);
});




Route::middleware('auth:sanctum')->group(function () {
  Route::post('/orders', [OrderController::class, 'store']);
  Route::post('/orders/status/{order_id}', [OrderController::class, 'UpdateStatus']);
  Route::get('/orders', [OrderController::class, 'AdminOrders']);
  Route::get('/my_orders', [OrderController::class, 'myOrders']);
  Route::get('/orders/{id}', [OrderController::class, 'show']);
  Route::post('/orders/{id}', [OrderController::class, 'update']);
});




Route::get('/iframes', [IframeController::class, 'index']);



//Social Media Routes 


Route::middleware('auth:sanctum')->prefix('social')->group(function () {

  //Facebook  
  Route::get('/read_facebook_posts', [SocialMediaController::class, 'ReadFacebookPosts']);
  Route::post('/post_facebook', [SocialMediaController::class, 'PostTOfacebook']);
  Route::post('/post_image_facebook', [SocialMediaController::class, 'PostImageTOfacebook']);

  //Instagram
  Route::get('/read_instagram_posts', [SocialMediaController::class, 'ReadInstagramPosts']);
  Route::post('/post_instagram', [SocialMediaController::class, 'PostTOInstagram']);

  //Youtube 

  Route::get('/get_youtube_current_channel', [SocialMediaController::class, 'GetYoutubeCurrentChannel']);
  Route::get('/get_youtube_current_channel_videos', [SocialMediaController::class, 'GetYoutubeCurrentChannelVideos']);
  Route::get('/get_youtube_video_stats', [SocialMediaController::class, 'GetYoutubeVideoStats']);
  Route::post('/youtube_publish_video', [SocialMediaController::class, 'PostTOYoutube']);
  Route::post('/youtube_change_video_status', [SocialMediaController::class, 'YoutubeChangeVideoStatus']);
});


Route::post('/payment/m5pnyyzcwr/webhook',  [PaymentSettingsController::class, 'HandlePaymentSuccess']);
