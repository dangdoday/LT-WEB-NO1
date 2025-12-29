<?php
require_once __DIR__ . '/../models/Classroom.php';

class ClassroomService
{
    /**
     * Create a classroom record and save uploaded avatar.
     * @param array $data ['name','description','building']
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

        $uploadDir = __DIR__ . '/../../web/image/classroom';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new RuntimeException('Unable to create upload dir');
            }
        }

        // upload file
        $filename = 'classroom_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        // store classroom record
        $relativePath = 'web/image/classroom/' . $filename;

        $id = Classroom::create([
            'name' => $data['name'] ?? null,
            'avatar' => $relativePath,
            'description' => $data['description'] ?? null,
            'building' => $data['building'] ?? null,

        ]);

        return ['id' => $id, 'avatar' => $relativePath, "description" => $data['description'], "building" => $data['building']];
    }

    public function update($id, array $data, $file = null)
    {
        $avatarPath = null;
        if ($file && isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            // reuse upload logic
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

            $uploadDir = __DIR__ . '/../../web/image/classroom';
            if (!is_dir($uploadDir)) {
                 if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                     throw new RuntimeException('Unable to create upload dir');
                 }
            }

            $filename = 'classroom_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            $targetPath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('Failed to move uploaded file');
            }
            $avatarPath = 'web/image/classroom/' . $filename;
        }

        $updateData = [
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'building' => $data['building'] ?? null,
        ];
        
        if ($avatarPath) {
            $updateData['avatar'] = $avatarPath;
        }

        return Classroom::update($id, $updateData);
    }
}
