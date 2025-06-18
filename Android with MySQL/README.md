# Đáp Án Bài Tập Trắc Nghiệm Active Directory và DHCP

## Câu 16
**Hỏi:** Các thành phần nào sau đây KHÔNG có trong tập tin cơ sở dữ liệu của Active Directory (NTDS.DIT)?
- A. Schema
- B. DNS
- C. Components
- D. Global Catalog (PAS)

**Đáp án:** B. DNS
> DNS là dịch vụ riêng biệt, mặc dù thường được tích hợp với Active Directory, nhưng dữ liệu DNS được lưu trữ trong các zone file riêng, không phải trong NTDS.DIT.

## Câu 17
**Hỏi:** Quy ước đặt tên đối tượng nào trong Active Directory là duy nhất dựa trên giao thức LDAP (Lightweight Directory Access Protocol)?
- A. Distinguished Name (DN)
- B. Globally Unique Identifier (GUID)
- C. Relative Distinguished Name (RDN)
- D. User Principal Name (UPN)

**Đáp án:** A. Distinguished Name (DN)
> DN xác định vị trí chính xác của một đối tượng trong cấu trúc phân cấp của Active Directory bằng cách liệt kê đường dẫn đầy đủ từ đối tượng đến gốc của cây thư mục.

## Câu 18
**Hỏi:** Lược đồ (Schema) trong Active Directory, được định nghĩa gồm 2 loại object là:
- A. Schema CategoryID Objects và Schema Attribute Objects
- B. Schema Class Objects và Schema Attribute Objects
- C. Schema Class Objects và Schema Account Objects
- D. Schema CategoryID Objects và Schema Account Objects

**Đáp án:** B. Schema Class Objects và Schema Attribute Objects
> Schema Class Objects định nghĩa các loại đối tượng có thể được tạo trong AD, còn Schema Attribute Objects định nghĩa các thuộc tính mà mỗi đối tượng có thể có.

## Câu 19
**Hỏi:** Những ký tự nào sau đây KHÔNG dùng để đặt tên cho Domain User?
- A. +
- B. ?
- C. *
- D. Tất cả đáp án đều đúng

**Đáp án:** D. Tất cả đáp án đều đúng
> Cả ba ký tự (+, ?, *) đều không được phép sử dụng trong tên tài khoản người dùng vì có ý nghĩa đặc biệt trong hệ thống.

## Câu 20
**Hỏi:** Trong các User Profile sau, User Profile nào sẽ bị xóa khi người dùng kết thúc session (phiên làm việc của user)?
- A. Mandatory User Profile
- B. Local User Profile
- C. Temporary User Profile
- D. Roaming User Profile

**Đáp án:** C. Temporary User Profile
> Temporary User Profile được tạo ra khi hệ thống không thể tải hồ sơ người dùng thông thường và sẽ tự động bị xóa khi người dùng đăng xuất.

## Câu 21
**Hỏi:** Hậu hiện làm quản trị mạng tại công ty ACB, hệ thống mạng hiện tại đang sử dụng máy chủ Windows 2012 domain controller quản lý miền "acb.com.vn". Ban giám đốc mong muốn các khách hàng của mình có thể sử dụng tài khoản để kết nối đến hệ thống mạng của công ty để tham khảo các sản phẩm của công ty. Hậu tạo một tài khoản khachhang. Ngay sau khi triển khai, khách hàng phàn nàn rằng họ không thể kết nối đến Server được. Hậu kiểm tra lại và thấy rằng tài khoản yêu cầu thay đổi mật khẩu khi khách đăng nhập lần đầu tiên. Hậu cần phải làm gì để không cho phép thay đổi mật khẩu của tài khoản khi khách hàng đăng nhập lần đầu?

**Đáp án:** B. Vào công cụ Active Directory Users and Computers, đổi mật khẩu của tài khoản khachhang trở về mật khẩu ban đầu. Sau đó nhấp phải vào tài khoản khachhang, chọn Properties --> chọn Tab Account, tại mục Account Options, bỏ chọn mục: "User must change password at next logon"
> Cần bỏ tùy chọn yêu cầu thay đổi mật khẩu khi đăng nhập lần đầu cho tài khoản cụ thể.

## Câu 22
**Hỏi:** Active Directory Federation Services (AD FS) là gì?

**Đáp án:** C. là một dịch vụ cung cấp cơ chế đăng nhập - single sign-on (SSO), cho phép bạn đăng nhập chỉ một lần nhưng có thể dùng nhiều ứng dụng Web có quan hệ với nhau.
> AD FS cung cấp cơ chế đăng nhập một lần, cho phép người dùng truy cập nhiều ứng dụng web khác nhau mà không cần đăng nhập lại.

## Câu 23
**Hỏi:** Tại hệ thống mạng của công ty Quảng cáo NT đã cài đặt và cấu hình DHCP server. Công ty hiện triển khai thêm một vài Access point mới và muốn thay đổi vùng IP được cấp động nhiều hơn cho các Access point này, bạn sẽ làm gì để thực hiện việc cấp IP cho thiết bị mới như yêu cầu?

**Đáp án:** A. Xóa và tạo lại Scope.
> Scope là phạm vi địa chỉ IP mà DHCP server có thể cấp phát. Khi cần thay đổi vùng IP, cách hiệu quả nhất là xóa Scope hiện tại và tạo lại Scope mới với dải địa chỉ IP rộng hơn.

