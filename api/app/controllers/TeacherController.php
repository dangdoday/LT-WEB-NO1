<?php
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../helpers/response.php';

class TeacherController
{
    public function index()
    {
        $teachers = Teacher::allFull();
        jsonResponse($teachers);
    }

    // Loại bỏ dấu tiếng Việt cho so sánh không dấu
    private function vnToAscii($str)
    {
        if ($str === null) return '';
        $str = mb_strtolower($str, 'UTF-8');
        $map = [
            'àáạảãâầấậẩẫăằắặẳẵ' => 'a',
            'èéẹẻẽêềếệểễ' => 'e',
            'ìíịỉĩ' => 'i',
            'òóọỏõôồốộổỗơờớợởỡ' => 'o',
            'ùúụủũưừứựửữ' => 'u',
            'ỳýỵỷỹ' => 'y',
            'đ' => 'd',
        ];
        foreach ($map as $viet => $ascii) {
            $str = preg_replace('/[' . $viet . ']/u', $ascii, $str);
        }
        return $str;
    }

    public function search()
    {
        $params = $_GET;
        $specialized = isset($params['specialized']) ? $params['specialized'] : null;
        $keyword = isset($params['keyword']) ? $params['keyword'] : null;
        $rows = Teacher::search($specialized, null);

        // Nếu không có keyword thì trả luôn
        if (empty($keyword)) {
            jsonResponse($rows);
            return;
        }

        // Map degree code -> label
        $degreeMap = [
            '001' => 'Cử nhân',
            '002' => 'Thạc sĩ',
            '003' => 'Tiến sĩ',
            '004' => 'Phó giáo sư',
            '005' => 'Giáo sư',
        ];

        $kw = $this->vnToAscii($keyword);
        $result = [];
        foreach ($rows as $row) {
            $nameAscii = $this->vnToAscii($row['name']);
            $descAscii = $this->vnToAscii($row['description']);
            $degreeLabel = $degreeMap[$row['degree']] ?? '';
            $degreeAscii = $this->vnToAscii($degreeLabel);
            if (
                strpos($nameAscii, $kw) !== false ||
                strpos($descAscii, $kw) !== false ||
                strpos($degreeAscii, $kw) !== false
            ) {
                $result[] = $row;
            }
        }
        jsonResponse($result);
    }

    public function specializations()
    {
        $specializations = Teacher::getSpecializedList();
        jsonResponse($specializations);
    }

    public function show($id)
    {
        $teacher = Teacher::findById($id);
        if ($teacher) {
            jsonResponse($teacher);
        } else {
            jsonResponse(['error' => 'Teacher not found'], 404);
        }
    }

    public function delete()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'ID is required'], 400);
            return;
        }
        try {
            // Lấy thông tin giáo viên trước khi xóa để lấy tên file avatar
            $teacher = Teacher::findById($id);
            
            $result = Teacher::deleteById($id);
            if ($result) {
                // Xóa file avatar nếu có
                if ($teacher && !empty($teacher['avatar'])) {
                    $avatarPath = __DIR__ . '/../../web/image/avatar/' . $teacher['avatar'];
                    if (file_exists($avatarPath)) {
                        unlink($avatarPath);
                    }
                }
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['error' => 'Không thể xoá giáo viên. Có thể giáo viên này đã từng mượn thiết bị hoặc có dữ liệu liên quan.'], 409);
            }
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') { // Foreign key constraint
                jsonResponse(['error' => 'Không thể xoá giáo viên vì đã từng mượn thiết bị hoặc có dữ liệu liên quan.'], 409);
            } else {
                jsonResponse(['error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
            }
        }
    }

    public function update()
    {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) {
            jsonResponse(['error' => 'ID is required'], 400);
            return;
        }

        // Fetch existing teacher data
        $existing = Teacher::findById($id);
        if (!$existing) {
            jsonResponse(['error' => 'Teacher not found'], 404);
            return;
        }


        // Lấy dữ liệu từ multipart/form-data (FormData) hoặc JSON
        $data = [];
        if (isset($_FILES['avatar'])) {
            // Handle file upload
            $file = $_FILES['avatar'];
            if ($file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../web/image/avatar/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid() . '_' . basename($file['name']);
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $data['avatar'] = $filename;
                } else {
                    jsonResponse(['error' => 'Failed to upload avatar'], 500);
                    return;
                }
            }
            // Khi dùng FormData, các trường text sẽ nằm trong $_POST (nếu enctype="multipart/form-data") hoặc $_REQUEST
            // Để chắc chắn, merge $_REQUEST (chứa cả $_POST và $_GET, trừ file)
            foreach(['name','specialized','degree','description'] as $field) {
                if (isset($_REQUEST[$field])) {
                    $data[$field] = $_REQUEST[$field];
                }
            }
        } else if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        } else {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            if ($jsonData) {
                $data = array_merge($data, $jsonData);
            }
        }

        // Merge từng trường, nếu không gửi lên hoặc gửi rỗng thì giữ nguyên giá trị cũ
        $mergedData = [
            'name' => (isset($data['name']) && trim($data['name']) !== '') ? $data['name'] : $existing['name'],
            'specialized' => (isset($data['specialized']) && trim($data['specialized']) !== '') ? $data['specialized'] : $existing['specialized'],
            'degree' => (isset($data['degree']) && trim($data['degree']) !== '') ? $data['degree'] : $existing['degree'],
            'description' => (isset($data['description']) && trim($data['description']) !== '') ? $data['description'] : $existing['description'],
            'avatar' => (isset($data['avatar']) && trim($data['avatar']) !== '') ? $data['avatar'] : $existing['avatar'],
        ];

        $result = Teacher::updateWithAvatar($id, $mergedData);
        jsonResponse(['success' => $result]);
    }
}
