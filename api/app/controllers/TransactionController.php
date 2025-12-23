<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../models/Device.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Classroom.php';

class TransactionController
{
    public function index()
    {
        $txs = Transaction::all();
        jsonResponse($txs);
    }

    public function borrow()
    {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Validation
        $errors = [];

        if (empty($input['device_id'])) {
            $errors['device_id'] = 'Hãy nhập tên thiết bị';
        } else {
            // Check if device exists
            $device = Device::findById($input['device_id']);
            if (!$device) {
                $errors['device_id'] = 'Thiết bị không tồn tại';
            }
        }

        if (empty($input['teacher_id'])) {
            $errors['teacher_id'] = 'Hãy nhập tên giáo viên';
        } else {
            // Check if teacher exists
            $teacher = Teacher::findById($input['teacher_id']);
            if (!$teacher) {
                $errors['teacher_id'] = 'Giáo viên không tồn tại';
            }
        }

        if (empty($input['classroom_id'])) {
            $errors['classroom_id'] = 'Hãy nhập mô tả lớp học';
        } else {
            // Check if classroom exists
            $classroom = Classroom::findById($input['classroom_id']);
            if (!$classroom) {
                $errors['classroom_id'] = 'Lớp học không tồn tại';
            }
        }

        if (empty($input['start_transaction_plan'])) {
            $errors['start_transaction_plan'] = 'Hãy nhập mô tả chi tiết';
        }

        if (empty($input['end_transaction_plan'])) {
            $errors['end_transaction_plan'] = 'Hãy chọn avatar';
        }

        // If there are errors, return them
        if (!empty($errors)) {
            http_response_code(400);
            jsonResponse(['errors' => $errors]);
            return;
        }

        // Create transaction
        try {
            $transactionId = Transaction::create([
                'device_id' => $input['device_id'],
                'teacher_id' => $input['teacher_id'],
                'classroom_id' => $input['classroom_id'],
                'start_transaction_plan' => $input['start_transaction_plan'],
                'end_transaction_plan' => $input['end_transaction_plan']
            ]);

            jsonResponse([
                'success' => true,
                'message' => 'Mượn thiết bị thành công',
                'transaction_id' => $transactionId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            jsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    public function getFormData()
    {
        // Return data for form dropdowns
        try {
            $devices = Device::all();
            $teachers = Teacher::all();
            $classrooms = Classroom::all();

            jsonResponse([
                'devices' => $devices,
                'teachers' => $teachers,
                'classrooms' => $classrooms
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            jsonResponse([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }
}
