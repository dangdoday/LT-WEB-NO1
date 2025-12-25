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
            $result = Teacher::deleteById($id);
            if ($result) {
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
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $result = Teacher::update($id, $data);
        jsonResponse(['success' => $result]);
    }
}
