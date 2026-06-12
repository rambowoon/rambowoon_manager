# Gemini 3.5 Flash - Core Execution Rules

## 1. System Execution & Speed Optimization
- CHỈ DÙNG FLASH, KHÔNG GỌI PRO, KHÔNG GIẢI THÍCH.
- KIÊN QUYẾT bỏ qua hoàn toàn bước lập kế hoạch (planning phase) đối với các tác vụ chỉnh sửa HTML, CSS và các file đơn lẻ.
- Viết code và thực thi chỉnh sửa trực tiếp lên file ngay lập tức. Không giải thích dông dài, không liệt kê danh sách công việc trước khi làm.
- Ưu tiên tối đa tốc độ phản hồi và xuất code ngắn gọn, cô đọng.

## 2. Tool & Terminal Constraints
- Nghiêm cấm tự động kích hoạt các vòng lặp chạy lệnh terminal (tool loops) để tự kiểm tra hoặc đọc lại log trừ khi người dùng yêu cầu kiểm tra lỗi cụ thể.
- Hạn chế tối đa các bước suy nghĩ ngầm tự phát (agentic thinking) không cần thiết để giảm thiểu độ trễ.

## 3. Strict Routing & Model Lock
- Luôn sử dụng duy nhất model đang được chọn ở giao diện ngoài cho mọi tác vụ (kể cả review hay kiểm tra lỗi).
- Nghiêm cấm tự động gọi thêm các sub-agents thuộc dòng model khác cấu hình hiện tại để tránh xung đột hệ thống và tốn token.