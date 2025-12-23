<template>
  <div class="borrow-device-container">
    <h1>Mượn Thiết Bị</h1>
    
    <form @submit.prevent="handleSubmit" class="borrow-form">
      <!-- Tên thiết bị -->
      <div class="form-group">
        <label for="device_id">
          Tên thiết bị <span class="required">*</span>
        </label>
        <select 
          id="device_id" 
          v-model="formData.device_id"
          :class="{ 'error': errors.device_id }"
        >
          <option value="">-- Chọn thiết bị --</option>
          <option 
            v-for="device in devices" 
            :key="device.id" 
            :value="device.id"
          >
            {{ device.name }}
          </option>
        </select>
        <span v-if="errors.device_id" class="error-message">
          {{ errors.device_id }}
        </span>
      </div>

      <!-- Giáo viên -->
      <div class="form-group">
        <label for="teacher_id">
          Giáo viên <span class="required">*</span>
        </label>
        <select 
          id="teacher_id" 
          v-model="formData.teacher_id"
          :class="{ 'error': errors.teacher_id }"
        >
          <option value="">-- Chọn giáo viên --</option>
          <option 
            v-for="teacher in teachers" 
            :key="teacher.id" 
            :value="teacher.id"
          >
            {{ teacher.name }}
          </option>
        </select>
        <span v-if="errors.teacher_id" class="error-message">
          {{ errors.teacher_id }}
        </span>
      </div>

      <!-- Lớp học -->
      <div class="form-group">
        <label for="classroom_id">
          Lớp học <span class="required">*</span>
        </label>
        <select 
          id="classroom_id" 
          v-model="formData.classroom_id"
          :class="{ 'error': errors.classroom_id }"
        >
          <option value="">-- Chọn lớp học --</option>
          <option 
            v-for="classroom in classrooms" 
            :key="classroom.id" 
            :value="classroom.id"
          >
            {{ classroom.name }}
          </option>
        </select>
        <span v-if="errors.classroom_id" class="error-message">
          {{ errors.classroom_id }}
        </span>
      </div>

      <!-- Thời gian bắt đầu -->
      <div class="form-group">
        <label for="start_transaction_plan">
          Thời gian bắt đầu <span class="required">*</span>
        </label>
        <input 
          type="datetime-local" 
          id="start_transaction_plan" 
          v-model="formData.start_transaction_plan"
          :class="{ 'error': errors.start_transaction_plan }"
        />
        <span v-if="errors.start_transaction_plan" class="error-message">
          {{ errors.start_transaction_plan }}
        </span>
      </div>

      <!-- Thời gian kết thúc -->
      <div class="form-group">
        <label for="end_transaction_plan">
          Thời gian kết thúc <span class="required">*</span>
        </label>
        <input 
          type="datetime-local" 
          id="end_transaction_plan" 
          v-model="formData.end_transaction_plan"
          :class="{ 'error': errors.end_transaction_plan }"
        />
        <span v-if="errors.end_transaction_plan" class="error-message">
          {{ errors.end_transaction_plan }}
        </span>
      </div>

      <!-- Submit button -->
      <div class="form-actions">
        <button type="submit" class="btn-submit" :disabled="isSubmitting">
          {{ isSubmitting ? 'Đang xử lý...' : 'Mượn' }}
        </button>
      </div>

      <!-- Success message -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>
    </form>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'BorrowDevice',
  setup() {
    const formData = ref({
      device_id: '',
      teacher_id: '',
      classroom_id: '',
      start_transaction_plan: '',
      end_transaction_plan: ''
    });

    const errors = ref({});
    const devices = ref([]);
    const teachers = ref([]);
    const classrooms = ref([]);
    const isSubmitting = ref(false);
    const successMessage = ref('');

    // Load form data (devices, teachers, classrooms)
    const loadFormData = async () => {
      try {
        const response = await fetch('/api/transactions/form-data');
        const data = await response.json();
        
        devices.value = data.devices || [];
        teachers.value = data.teachers || [];
        classrooms.value = data.classrooms || [];
      } catch (error) {
        console.error('Error loading form data:', error);
      }
    };

    // Validate form
    const validateForm = () => {
      const newErrors = {};

      if (!formData.value.device_id) {
        newErrors.device_id = 'Hãy nhập tên thiết bị';
      }

      if (!formData.value.teacher_id) {
        newErrors.teacher_id = 'Hãy nhập tên giáo viên';
      }

      if (!formData.value.classroom_id) {
        newErrors.classroom_id = 'Hãy nhập mô tả lớp học';
      }

      if (!formData.value.start_transaction_plan) {
        newErrors.start_transaction_plan = 'Hãy nhập mô tả chi tiết';
      }

      if (!formData.value.end_transaction_plan) {
        newErrors.end_transaction_plan = 'Hãy chọn avatar';
      }

      errors.value = newErrors;
      return Object.keys(newErrors).length === 0;
    };

    // Handle form submission
    const handleSubmit = async () => {
      // Clear previous messages
      successMessage.value = '';
      
      // Validate
      if (!validateForm()) {
        return;
      }

      isSubmitting.value = true;

      try {
        const response = await fetch('/api/transactions/borrow', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formData.value)
        });

        const data = await response.json();

        if (response.ok && data.success) {
          successMessage.value = data.message || 'Mượn thiết bị thành công!';
          
          // Reset form
          formData.value = {
            device_id: '',
            teacher_id: '',
            classroom_id: '',
            start_transaction_plan: '',
            end_transaction_plan: ''
          };
          errors.value = {};

          // Hide success message after 3 seconds
          setTimeout(() => {
            successMessage.value = '';
          }, 3000);
        } else {
          // Server-side validation errors
          if (data.errors) {
            errors.value = data.errors;
          } else {
            alert(data.message || 'Có lỗi xảy ra!');
          }
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        alert('Có lỗi xảy ra khi gửi form!');
      } finally {
        isSubmitting.value = false;
      }
    };

    onMounted(() => {
      loadFormData();
    });

    return {
      formData,
      errors,
      devices,
      teachers,
      classrooms,
      isSubmitting,
      successMessage,
      handleSubmit
    };
  }
};
</script>

<style scoped>
.borrow-device-container {
  max-width: 600px;
  margin: 50px auto;
  padding: 30px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
  text-align: center;
  color: #333;
  margin-bottom: 30px;
}

.borrow-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: 600;
  margin-bottom: 8px;
  color: #555;
}

.required {
  color: #e74c3c;
}

select,
input[type="datetime-local"] {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

select:focus,
input[type="datetime-local"]:focus {
  outline: none;
  border-color: #3498db;
}

select.error,
input.error {
  border-color: #e74c3c;
}

.error-message {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 5px;
}

.form-actions {
  margin-top: 10px;
}

.btn-submit {
  width: 100%;
  padding: 12px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-submit:hover:not(:disabled) {
  background-color: #2980b9;
}

.btn-submit:disabled {
  background-color: #95a5a6;
  cursor: not-allowed;
}

.success-message {
  padding: 15px;
  background-color: #2ecc71;
  color: white;
  border-radius: 4px;
  text-align: center;
  margin-top: 20px;
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
