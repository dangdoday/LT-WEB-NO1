<?php
require_once __DIR__ . '/../models/Teacher.php';

class TeacherService
{
    /**
     * Create a teacher record and save uploaded avatar.
     * @param array $data ['name','specialized','degree','description']
     * @param array $file $_FILES['avatar']
     * @return array ['id'=>int,'avatar'=>string]
     * @throws Exception on failure
     */
    public function create(array $data, array $file)
    {
        // validate file type
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('No uploaded file');
        }

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

        // upload file
        $filename = 'teacher_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        // store teacher record
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

    public function update($id, array $data, ?array $file = null)
    {
        $updateData = [
            'name' => $data['name'] ?? null,
            'specialized' => $data['specialized'] ?? null,
            'degree' => $data['degree'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        // Handle file upload if exists
        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            // Validate and upload similar to create
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
            
            // TODO: Ideally delete old avatar here but skipping for simplicity
        }

        return Teacher::updateWithAvatar($id, $updateData);
    }
}
