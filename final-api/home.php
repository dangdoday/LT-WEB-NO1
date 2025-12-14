<?php
session_start();

// Chặn truy cập trực tiếp
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}

$login_id = $_SESSION['login_id'];
// Format lại thời gian một chút nếu cần, hoặc để nguyên
$login_time = $_SESSION['login_time']; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            display: flex;
            justify-content: center;
            padding-top: 50px; /* Cách nắp trên một chút */
            margin: 0;
        }

        .main-container {
            width: 900px; /* Độ rộng khung bao */
            border: 1px solid #4a7ebb; /* Viền xanh giống ảnh */
            padding: 40px;
            background-color: white;
            min-height: 400px; /* Chiều cao tối thiểu cho đẹp */
        }

        /* Phần thông tin login */
        .info-section {
            margin-bottom: 50px;
            font-size: 14px;
            line-height: 1.6;
            color: #000;
        }

        /* Phần Menu chia cột */
        .menu-grid {
            display: flex;
            justify-content: space-between; /* Chia đều khoảng cách các cột */
            align-items: flex-start;
        }

        .menu-column {
            flex: 1; /* Mỗi cột chiếm độ rộng bằng nhau */
            padding-right: 10px;
        }

        .menu-title {
            font-size: 15px;
            margin-bottom: 10px;
            color: #000;
            font-weight: normal; /* Trong ảnh tiêu đề không quá đậm */
        }

        .menu-links a {
            display: block; /* Mỗi link nằm trên 1 dòng */
            color: blue;
            text-decoration: underline;
            font-size: 14px;
            margin-bottom: 6px;
            width: fit-content;
        }

        .menu-links a:hover {
            color: #00008B; /* Màu tối hơn khi hover */
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="info-section">
            <div>Tên login: <?= htmlspecialchars($login_id) ?></div>
            <div>Thời gian login: <?= htmlspecialchars($login_time) ?></div>
        </div>

        <div class="menu-grid">
            
            <div class="menu-column">
                <div class="menu-title">Phòng học</div>
                <div class="menu-links">
                    <a href="#">Tìm kiếm</a>
                    <a href="#">Thêm mới</a>
                </div>
            </div>

            <div class="menu-column">
                <div class="menu-title">Giáo viên</div>
                <div class="menu-links">
                    <a href="#">Tìm kiếm</a>
                    <a href="#">Thêm mới</a>
                </div>
            </div>

            <div class="menu-column">
                <div class="menu-title">Thiết bị</div>
                <div class="menu-links">
                    <a href="#">Tìm kiếm</a>
                    <a href="#">Thêm mới</a>
                </div>
            </div>

            <div class="menu-column">
                <div class="menu-title">Mượn/trả thiết bị</div>
                <div class="menu-links">
                    <a href="#">Tìm kiếm</a>
                    <a href="#">Tìm kiếm nâng cao</a>
                    <a href="#">Trả thiết bị</a>
                    <a href="#">Lịch sử mượn thiết bị</a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>