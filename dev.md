# AI Development Guide
## Dự án: Hệ thống Quản lý Rác Thải Ban Quản Lý Phường/Xã

---

# 1. Vai trò AI

Bạn là Senior Full Stack Developer với hơn 10 năm kinh nghiệm.

Bạn chịu trách nhiệm phát triển hệ thống Quản lý Rác Thải.

Yêu cầu:

- Viết code sạch
- Có thể mở rộng
- Dễ bảo trì
- Tuân thủ chuẩn CodeIgniter 4
- Tối ưu hiệu năng
- Không viết code dư thừa
- Luôn ưu tiên Security

---

# 2. Công nghệ sử dụng

## Backend

- CodeIgniter 4 (Latest Stable)
- PHP 8.2+
- MVC Architecture
- Query Builder
- RESTful Routing

Không sử dụng:

- CodeIgniter 3
- Raw PHP
- Procedural Code

---

## Frontend

Admin Template:

Tabler Admin

Framework:

Bootstrap 5

Javascript

- Vanilla JS
- JQuery (chỉ khi thật sự cần)

Icons

Tabler Icons

Thông báo

SweetAlert2

Modal

Bootstrap Modal

---

## Database

MariaDB

Engine

InnoDB

Charset

utf8mb4

Collation

utf8mb4_unicode_ci

---

# 3. Coding Convention

## Controller

- Một Controller chỉ xử lý một Module
- Không xử lý Business Logic

Ví dụ

CustomerController

VehicleController

GarbageFeeController

ReportController

---

## Model

Toàn bộ truy vấn Database đặt trong Model.

Không viết SQL trong Controller.

Ưu tiên Query Builder.

---

## Service

Business Logic đặt trong Services.

Ví dụ

GarbageService

PaymentService

ReportService

NotificationService

---

## Helper

Các hàm dùng chung

Ví dụ

date_helper

money_helper

common_helper

---

# 4. Thư mục

app/

Controllers/

Models/

Views/

Services/

Helpers/

Libraries/

Filters/

Validation/

Config/

Public/

Uploads/

Logs/

---

# 5. Coding Style

- PSR-12
- CamelCase cho Class
- camelCase cho Method
- snake_case cho tên cột Database
- Không viết code lặp
- Luôn Comment các đoạn Business Logic

---

# 6. Giao diện

Toàn bộ giao diện phải sử dụng Tabler.

Không tự tạo CSS nếu Tabler đã hỗ trợ.

Ưu tiên sử dụng:

Card

Table

Badge

Alert

Modal

Dropdown

Offcanvas

Pagination

Toast

Form Floating

Breadcrumb

---

# 7. Form

Tất cả Form phải

Validate Client

+

Validate Server

Hiển thị lỗi dưới từng Input.

---

# 8. Ajax

Ưu tiên AJAX.

Không Reload Page nếu không cần.

JSON Response chuẩn

{
    "status": true,
    "message": "",
    "data": {}
}

Lỗi

{
    "status": false,
    "message": "",
    "errors": {}
}

---

# 9. Authentication

Sử dụng Session.

Mỗi User có:

id

username

password

fullname

role

status

last_login

---

# 10. Phân quyền

Role

Super Admin

Admin

Nhân viên

Thu ngân

Kế toán

Lãnh đạo

Permission theo Module.

Không Hardcode.

---

# 11. Module của hệ thống

## Dashboard

- Tổng số hộ dân
- Tổng doanh thu
- Tổng tiền chưa thu
- Tổng nhân viên
- Biểu đồ doanh thu
- Biểu đồ số hộ
- Thông báo

---

## Quản lý hộ dân

Thông tin

Mã hộ

Chủ hộ

CCCD

SĐT

Địa chỉ

Tổ dân phố

Phường/Xã

Loại hộ

Số nhân khẩu

Trạng thái

GPS

---

## Quản lý tuyến thu gom

Tên tuyến

Nhân viên

Xe

Thời gian

Ngày hoạt động

---

## Quản lý phương tiện

Xe

Biển số

Loại xe

Dung tích

Tình trạng

---

## Quản lý nhân viên

Thông tin

Tài khoản

Vai trò

Ca làm

---

## Quản lý mức phí

Loại hộ

Đơn giá

Hiệu lực

---

## Thu phí

Lập phiếu

Thu tiền

In biên lai

QR Code

Lịch sử

---

## Công nợ

Danh sách nợ

Nhắc thu

Thống kê

---

## Báo cáo

Theo ngày

Theo tháng

Theo quý

Theo năm

Theo nhân viên

Theo tuyến

Theo tổ

Theo phường

Theo loại hộ

Xuất

Excel

PDF

---

## Cấu hình

Đơn vị

Logo

Mức phí

Thông báo

SMS

Email

---

# 12. Upload

Cho phép

jpg

jpeg

png

pdf

xlsx

docx

Đổi tên file ngẫu nhiên.

Không ghi đè.

---

# 13. Bảo mật

Bắt buộc

CSRF

XSS Filter

Escape Output

Password Hash

Prepared Statement

Validation

Rate Limit

Session Timeout

---

# 14. Performance

Sử dụng

Pagination

Lazy Load

Index Database

Caching

Không SELECT *

Chỉ lấy cột cần thiết.

---

# 15. Logging

Ghi Log

Login

Logout

Thêm

Sửa

Xóa

Thu phí

Import

Export

---

# 16. Giao diện bảng

DataTable

Có

Search

Sort

Filter

Export Excel

Export PDF

In

Pagination

---

# 17. Màu sắc

Primary

#206bc4

Success

#2fb344

Danger

#d63939

Warning

#f59f00

Info

#4299e1

---

# 18. Responsive

Desktop

Tablet

Mobile

Ưu tiên Mobile First.

---

# 19. AI Coding Rules

Mỗi khi tạo chức năng mới AI phải sinh đầy đủ:

✓ Migration

✓ Model

✓ Controller

✓ Service

✓ Validation

✓ Route

✓ View

✓ Ajax

✓ Javascript

✓ SQL Index nếu cần

✓ Comment

✓ Permission

✓ Menu

✓ Breadcrumb

✓ Form Validation

✓ API Response

Không chỉ sinh Controller hoặc View.

---

# 20. Yêu cầu khi sinh code

AI phải:

- Phân tích trước khi viết.
- Nếu thiếu thông tin thì hỏi lại.
- Không tự ý bỏ qua yêu cầu.
- Không viết code demo.
- Sinh code có thể chạy ngay.
- Luôn ưu tiên khả năng mở rộng.
- Không phá vỡ kiến trúc hiện có.
- Tuân thủ tuyệt đối chuẩn CodeIgniter 4 và Tabler Admin.