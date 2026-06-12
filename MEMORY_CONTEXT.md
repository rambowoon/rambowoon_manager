# RamboWoon Manager - Deployment Memory Context

## 1. Hệ thống Bridge (v5.0 Platinum)
Đây là "bộ não" nằm trên các Hosting (Demo & Production).
- **Cơ chế Tự nhận diện:** Tự động quét và giải mã file `.env` để lấy thông tin Database (User, Pass, Name) mà không cần Manager truyền sang.
- **Bảo mật Đa lớp:**
    - **Self-Destruct:** Tự xóa `bridge.php` và `tools.php` ngay sau khi Deploy xong.
    - **_old Lock:** Tự đổi tên thư mục Demo thành `_old` sau khi người dùng tải file bàn giao thành công.
    - **lock.php Force:** Tự chèn `lock.php` vào `.htaccess` (DirectoryIndex) để khóa web Production.

## 2. Quy trình Publish Production
Quy trình này cực kỳ nghiêm ngặt để đảm bảo dữ liệu không bị hỏng:
- **Strict DA API Check:** Manager kiểm tra phản hồi từ DirectAdmin. Nếu không tạo được DB/Email, hệ thống sẽ dừng ngay lập tức và báo lỗi đỏ.
- **SQL Integrity:** Bridge kiểm tra dung lượng file SQL. Nếu dưới 100 bytes (rỗng), hệ thống sẽ không cho phép Deploy.
- **Propagation Delay:** Nghỉ 2 giây (`sleep(2)`) trước khi import để đợi DirectAdmin kích hoạt quyền Database.
- **Domain Mapping:** Tự động đổi tên miền Demo thành tên miền Production trong cơ sở dữ liệu.

## 3. Tiêu chuẩn Dữ liệu (UTF-8)
Hỗ trợ đầy đủ tiếng Việt cho các dự án Nasanic:
- **Charset:** `utf8mb4`
- **Collation:** `utf8mb4_unicode_ci` (Đã ép kiểu trong cả Export, Import và khởi tạo PDO).
- **SQL Header:** Mọi file SQL đều chứa chỉ thị `SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'`.

## 4. Trình quản lý dự án (UI Manager)
- **Auto-Lock UI:** Tự động khóa (disabled) các nút *Deploy Demo*, *Publish Production* và *Download Package* ngay khi thao tác đó thành công.
- **Sync Tools:** Chức năng bắt buộc phải chạy mỗi khi cập nhật tính năng mới cho Bridge để đồng bộ lên Host.

## 5. Lưu ý quan trọng
- Luôn nhấn **Sync Tools** cho dự án trước khi thực hiện các thao tác quan trọng để đảm bảo Host đang chạy bản `v5.0 Platinum`.
- Nếu gặp lỗi `Access denied` trên Production, hãy kiểm tra phản hồi của DirectAdmin trong bảng Log để xem DB đã thực sự được tạo chưa.
