# Hướng dẫn tích hợp AMP cho dự án Nasanic Framework

Thư mục này chứa toàn bộ các thành phần (Views, Layouts, Helpers, Controllers, Routes, Configs, Assets) đã được tối ưu hóa để chạy chuẩn AMP (Accelerated Mobile Pages) cho Trang chủ, Sản phẩm, Tin tức và các form liên hệ/newsletter không bị dính lỗi JavaScript/Turnstile Captcha.

---

## Các file đã bóc tách

1. **Giao diện AMP (Views):**
   * [src/Views/amp/](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Views/amp) - Chứa toàn bộ giao diện AMP (Trang chủ, chi tiết sản phẩm, chi tiết bài viết, menu, css, js...).
   * [src/Views/layout/master_amp.blade.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Views/layout/master_amp.blade.php) - Layout master chung cho mọi trang AMP.
   * [src/Views/templates/layout/head.blade.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Views/templates/layout/head.blade.php) - Bản sao file `head.blade.php` của giao diện Desktop có chèn thẻ khai báo liên kết AMP.
   * [src/Views/mobile/layout/head.blade.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Views/mobile/layout/head.blade.php) - Bản sao file `head.blade.php` của giao diện Mobile có chèn thẻ khai báo liên kết AMP.

2. **Cấu hình dự án (Configs):**
   * [config/app.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/config/app.php) - Chứa cấu hình tiền tố AMP: `'amp_prefix' => (env('SITE_PATH') . 'amp')`.
   * [config/view.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/config/view.php) - Cấu hình thư mục view AMP và composer view AMP:
     ```php
     'view_amp' => base_path('src/Views/amp'),
     'composer_amp' => \NASANICORE\Controllers\Web\AllController::class,
     ```

3. **Xử lý Breadcrumbs & AMP conversion (Helpers):**
   * [src/Helpers/BreadCrumbs.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Helpers/BreadCrumbs.php) - Helper tự động nhận diện URL hiện tại có tiền tố `/amp` hay không để sinh ra link điều hướng breadcrumb chuẩn xác.
   * [src/Helpers/Func.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Helpers/Func.php) - Helper chứa các hàm làm sạch và chuyển đổi nội dung HTML sang định dạng hợp lệ của AMP như `ampify` và `ampifyImageTag`.

4. **Tài nguyên tĩnh (Assets):**
   * [assets/amp/](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/assets/amp) - Chứa các tài nguyên tĩnh riêng dành cho giao diện AMP (fonts, icons, styles...).

5. **API & Form Submit (Controllers):**
   * [src/Controllers/Web/ApiController.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Controllers/Web/ApiController.php) - Xử lý gửi Form đăng ký nhận tin (newsletter) chuẩn AMP:
     * Tự động bỏ qua xác thực Cloudflare Turnstile khi yêu cầu gửi từ trang AMP (vì AMP không tải được JS Turnstile ngoài).
     * Trả về JSON chuẩn kèm CORS header `AMP-Access-Control-Allow-Source-Origin` khi gửi qua XHR (`action-xhr`).
     * Đảm bảo hàm `index()` trả về kết quả khớp (`return match`), tránh bị lỗi trắng trang khi submit.

6. **Khai báo Routes:**
   * [src/Routes/web.php](file:///d:/RBWStack/www/2026_06/thegreenuniverse_1005126w/AMP_NASANI/src/Routes/web.php) - Khai báo nhóm Route AMP với tiền tố `/amp`:
     ```php
     NASANIRouter::group(['namespace' => 'Web', 'prefix' => config('app.amp_prefix'), 'middleware' => [\NASANICORE\Middlewares\LangRequest::class, \NASANICORE\Middlewares\CheckRedirect::class]], function ($language = 'vi') {
         NASANIRouter::get('/', 'HomeController@index')->name('amp.home');
         NASANIRouter::get('/blog', 'NewsController@index')->name('amp.blog');
         NASANIRouter::get('/san-pham', 'ProductController@index')->name('amp.san-pham');
         NASANIRouter::get('/{slug}', 'SlugController@handle')->name('amp.slugweb');
     });
     ```

---

## Cách tích hợp vào dự án mới nhanh chóng

1. **Bước 1: Copy các thư mục đè lên**
   * Copy toàn bộ các thư mục `src`, `config`, và `assets` trong thư mục `AMP_NASANI` này chép đè vào thư mục gốc của dự án mới.

2. **Bước 2: Cấu hình ENV**
   * Đảm bảo file `.env` của dự án mới có khai báo tiền tố AMP:
     ```env
     AMP_PREFIX=amp
     ```

3. **Bước 3: Đăng ký Router**
   * Thêm nhóm Route `/amp` vào đầu file `src/Routes/web.php` của dự án mới như mô tả ở mục 6 phía trên.

4. **Bước 4: Khai báo liên kết AMP trên trang thường**
   * Đảm bảo chèn đoạn code sau vào các file `head.blade.php` chính của website (nếu không copy đè file head):
     ```html
     @if (($com ?? '') == 'trang-chu' || ($com ?? '') == 'san-pham')
         <link rel="amphtml" href="{{ Func::getCurrentPageURLAMP() }}" />
     @endif
     ```
