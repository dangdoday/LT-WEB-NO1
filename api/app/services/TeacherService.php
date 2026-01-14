<?php
require_once __DIR__ . '/../models/Teacher.php';

class TeacherService
{
    /**
     * Tạo bản ghi giáo viên và lưu ảnh đại diện đã tải lên.
     * Create a teacher record and save uploaded avatar.
     * @param array $data ['name','specialized','degree','description']
     * @param array $file $_FILES['avatar']
     * @return array ['id'=>int,'avatar'=>string]
     * @throws Exception on failure
     */
    public function create(array $data, array $file)
    {
        // Kiểm tra loại file hợp lệ (Validate file type)
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        // Kiểm tra xem có file được upload hay không
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('No uploaded file');
        }

        // Phát hiện loại MIME của file (Detect mime type)
        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($file['tmp_name']);
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
            if ($finfo) finfo_close($finfo);
        } else {
            $imageInfo = getimagesize($file['tmp_name']);
            $mime = $imageInfo['mime'] ?? false;
        }

        // Kiểm tra loại file có được phép hay không
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Invalid file type');
        }

        // Tạo thư mục upload nếu chưa tồn tại
        $uploadDir = __DIR__ . '/../../web/image/avatar';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new RuntimeException('Unable to create upload dir');
            }
        }

        // Tạo tên file duy nhất và di chuyển file (Upload file)
        $filename = 'teacher_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $targetPath = $uploadDir . '/' . $filename;

        // Di chuyển file đã upload vào thư mục đích
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        // Lưu thông tin giáo viên vào cơ sở dữ liệu (Store teacher record)
        $relativePath = 'web/image/avatar/' . $filename;

        $id = Teacher::create([
            'name' => $data['name'] ?? null,
            'specialized' => $data['specialized'] ?? null,
            'degree' => $data['degree'] ?? null,
            'description' => $data['description'] ?? null,
            'avatar' => $relativePath,
        ]);

        return ['id' => $id, 'avatar' => $relativePath, 'specialized' => $data['specialized'], 'degree' => $data['degree'], 'description' => $data['description']];
    }

    /**
     * Cập nhật thông tin giáo viên (Update teacher information)
     */
    public function update($id, array $data, ?array $file = null)
    {
        $updateData = [
            'name' => $data['name'] ?? null,
            'specialized' => $data['specialized'] ?? null,
            'degree' => $data['degree'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        // Xử lý upload file mới nếu có (Handle file upload if exists)
        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            // Kiểm tra và upload file tương tự như khi tạo mới
             $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            // detect mime
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($file['tmp_name']);
            } elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
                if ($finfo) finfo_close($finfo);
            } else {
                $imageInfo = getimagesize($file['tmp_name']);
                $mime = $imageInfo['mime'] ?? false;
            }

            if (!isset($allowed[$mime])) {
                throw new RuntimeException('Invalid file type');
            }

            $uploadDir = __DIR__ . '/../../web/image/avatar';
             if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    throw new RuntimeException('Unable to create upload dir');
                }
            }

            $filename = 'teacher_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            $targetPath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('Failed to move uploaded file');
            }

            $updateData['avatar'] = 'web/image/avatar/' . $filename;
            
        }

        return Teacher::updateWithAvatar($id, $updateData);
    }
}
