<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const step = ref("input");
const loading = ref(true);
const loadError = ref("");
const submitting = ref(false);
const serverError = ref("");

const form = reactive({
  device_id: "",
  teacher_id: "",
  classroom_id: "",
  start_transaction_plan: "",
  end_transaction_plan: "",
  comment: "",
});

const errors = reactive({
  device_id: "",
  teacher_id: "",
  classroom_id: "",
  start_transaction_plan: "",
  end_transaction_plan: "",
});

const options = reactive({
  devices: [],
  teachers: [],
  classrooms: [],
});

const apiBase = import.meta.env.VITE_API_BASE || "http://localhost:8000";

const selectedDevice = computed(() => {
  return options.devices.find(
    (item) => String(item.id) === String(form.device_id)
  );
});

const selectedTeacher = computed(() => {
  return options.teachers.find(
    (item) => String(item.id) === String(form.teacher_id)
  );
});

const selectedClassroom = computed(() => {
  return options.classrooms.find(
    (item) => String(item.id) === String(form.classroom_id)
  );
});

const resetErrors = () => {
  errors.device_id = "";
  errors.teacher_id = "";
  errors.classroom_id = "";
  errors.start_transaction_plan = "";
  errors.end_transaction_plan = "";
};

const validate = () => {
  resetErrors();
  let ok = true;

  if (!form.device_id) {
    errors.device_id = "Hãy chọn thiết bị.";
    ok = false;
  }
  if (!form.teacher_id) {
    errors.teacher_id = "Hãy chọn giáo viên.";
    ok = false;
  }
  if (!form.classroom_id) {
    errors.classroom_id = "Hãy chọn lớp học.";
    ok = false;
  }
  if (!form.start_transaction_plan) {
    errors.start_transaction_plan = "Hãy chọn thời gian bắt đầu.";
    ok = false;
  } else {
    const now = new Date();
    const startDate = new Date(form.start_transaction_plan);
    if (startDate <= now) {
      errors.start_transaction_plan = "Thời gian bắt đầu phải lớn hơn hiện tại.";
      ok = false;
    }
  }
  if (!form.end_transaction_plan) {
    errors.end_transaction_plan = "Hãy chọn thời gian kết thúc.";
    ok = false;
  } else if (form.start_transaction_plan) {
    const startDate = new Date(form.start_transaction_plan);
    const endDate = new Date(form.end_transaction_plan);
    if (endDate <= startDate) {
      errors.end_transaction_plan = "Thời gian kết thúc phải lớn hơn thời gian bắt đầu.";
      ok = false;
    }
  }

  return ok;
};

const goConfirm = () => {
  serverError.value = "";
  if (validate()) {
    step.value = "confirm";
  }
};

const goEdit = () => {
  step.value = "input";
};

const goHome = () => {
  router.push("/");
};

const submit = async () => {
  if (submitting.value) return;
  submitting.value = true;
  serverError.value = "";

  try {
    const response = await fetch(`${apiBase}/borrow_device.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        device_id: form.device_id,
        teacher_id: form.teacher_id,
        classroom_id: form.classroom_id,
        start_transaction_plan: form.start_transaction_plan,
        end_transaction_plan: form.end_transaction_plan,
        comment: form.comment.trim(),
      }),
    });

    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) {
            errors[key] = payload.fields[key];
          }
        });
      }
      serverError.value = payload.error || "Tạo phiếu mượn thất bại.";
      step.value = "input";
      return;
    }

    step.value = "complete";
  } catch (error) {
    serverError.value = "Không thể kết nối tới máy chủ.";
    step.value = "input";
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  const queryDeviceId = router.currentRoute.value.query.device_id;

  try {
    const response = await fetch(`${apiBase}/transaction_form.php`);
    const payload = await response.json();
    if (!response.ok) {
      throw new Error(payload.error || "Load failed");
    }
    options.devices = payload.devices || [];
    options.teachers = payload.teachers || [];
    options.classrooms = payload.classrooms || [];

    if (queryDeviceId) {
      form.device_id = queryDeviceId;
    }
  } catch (error) {
    loadError.value = "Không tải được dữ liệu. Vui lòng thử lại.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Borrow device</p>
          <h1>Mượn thiết bị</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Input</span>
          <span v-else-if="step === 'confirm'">Confirm</span>
          <span v-else>Complete</span>
        </div>
      </header>

      <div v-if="loading" class="loading">Đang tải dữ liệu...</div>
      <div v-else-if="loadError" class="error">{{ loadError }}</div>

      <form
        v-else-if="step === 'input'"
        class="form"
        @submit.prevent="goConfirm"
      >
        <div class="form-grid">
          <label for="device">Thiết bị</label>
          <div>
            <input
              id="device"
              type="text"
              :value="selectedDevice?.name || ''"
              placeholder="Thiết bị"
              readonly
            />
            <p v-if="errors.device_id" class="error">{{ errors.device_id }}</p>
          </div>

          <label for="teacher">Giáo viên</label>
          <div>
            <select id="teacher" v-model="form.teacher_id">
              <option disabled value="">Chọn giáo viên</option>
              <option
                v-for="item in options.teachers"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </option>
            </select>
            <p v-if="errors.teacher_id" class="error">
              {{ errors.teacher_id }}
            </p>
          </div>

          <label for="classroom">Lớp học</label>
          <div>
            <select id="classroom" v-model="form.classroom_id">
              <option disabled value="">Chọn lớp học</option>
              <option
                v-for="item in options.classrooms"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </option>
            </select>
            <p v-if="errors.classroom_id" class="error">
              {{ errors.classroom_id }}
            </p>
          </div>

          <label for="start">Bắt đầu</label>
          <div>
            <input
              id="start"
              v-model="form.start_transaction_plan"
              type="datetime-local"
            />
            <p v-if="errors.start_transaction_plan" class="error">
              {{ errors.start_transaction_plan }}
            </p>
          </div>

          <label for="end">Kết thúc</label>
          <div>
            <input
              id="end"
              v-model="form.end_transaction_plan"
              type="datetime-local"
            />
            <p v-if="errors.end_transaction_plan" class="error">
              {{ errors.end_transaction_plan }}
            </p>
          </div>

          <label for="comment">Ghi chú</label>
          <div>
            <textarea
              id="comment"
              v-model="form.comment"
              rows="4"
              placeholder="Mô tả thêm (nếu có)"
            ></textarea>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Xác nhận</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Giáo viên</span>
          <span class="value">{{ selectedTeacher?.name || "-" }}</span>

          <span class="label">Thiết bị</span>
          <span class="value">{{ selectedDevice?.name || "-" }}</span>

          <span class="label">Lớp học</span>
          <span class="value">{{ selectedClassroom?.name || "-" }}</span>

          <span class="label">Bắt đầu</span>
          <span class="value">{{ form.start_transaction_plan }}</span>

          <span class="label">Kết thúc</span>
          <span class="value">{{ form.end_transaction_plan }}</span>

          <span class="label">Ghi chú</span>
          <div class="value description-box">{{ form.comment || "-" }}</div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Sửa lại</button>
          <button
            type="button"
            class="primary"
            :disabled="submitting"
            @click="submit"
          >
            {{ submitting ? "Đang gửi..." : "Đăng ký mượn" }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Đăng ký mượn thành công</h2>
          <p>Yêu cầu mượn thiết bị đã được ghi nhận.</p>
          <button type="button" class="primary" @click="goHome">
            Trở về trang chủ
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap");

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.card {
  width: min(920px, 100%);
  background: #fffdf8;
  border: 1px solid #cdd7e5;
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

.card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
}

.card__header h1 {
  margin: 8px 0 0;
  font-size: 32px;
  letter-spacing: -0.02em;
}

.eyebrow {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 0.14em;
  color: #5b6475;
  margin: 0;
}

.step-pill {
  padding: 8px 16px;
  border-radius: 999px;
  background: rgba(46, 109, 180, 0.12);
  color: #2e6db4;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.1em;
}

/* === PHẦN LAYOUT GRID (GIỐNG RETURN DEVICE) === */
.form-grid {
  display: grid;
  grid-template-columns: 160px auto; /* Tự động co giãn theo nội dung input */
  gap: 16px 20px;
}

label,
.label {
  font-weight: 600;
  color: #5b6475;
  align-self: center; /* Căn giữa label theo chiều dọc */
}

/* === PHẦN INPUT (FIX CỨNG 400PX) === */
input,
select,
textarea {
  /* Dùng !important để đảm bảo độ rộng chuẩn 400px */
  width: 400px !important;
  max-width: 100% !important;

  box-sizing: border-box;
  border: 1px solid #cdd7e5;
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
  line-height: 1.5;
  transition: border-color 0.2s ease;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #2e6db4;
}
/* =========================================== */

select {
  height: 44px;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%235b6475' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding: 2px 14px;
  padding-right: 40px;
}

select option {
  padding: 12px 8px;
}

::placeholder {
  color: #9ca3af;
  opacity: 1;
}

input[readonly] {
  background-color: #f9fafb;
  cursor: not-allowed;
  color: #1d2330;
}

input[type="datetime-local"] {
  height: 44px;
  cursor: pointer;
}

textarea {
  resize: vertical;
  min-height: 120px;
  padding: 12px 14px;
}

.actions {
  margin-top: 28px;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: center;
}

button {
  border: none;
  border-radius: 14px;
  padding: 12px 24px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

button.primary {
  background: #2e6db4;
  color: white;
  box-shadow: 0 10px 20px rgba(46, 109, 180, 0.2);
}

button.ghost {
  background: #eef2f9;
  color: #2e6db4;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  margin: 6px 0 0;
  color: #b13535;
  font-size: 13px;
}

.confirm .value {
  font-weight: 600;
  align-self: center;
}

.description-box {
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  padding: 14px;
  background: #fff;
  min-height: 120px;
  white-space: pre-wrap;
  width: 400px !important; /* Fix luôn cái ô review ghi chú */
  max-width: 100% !important;
}

.complete {
  text-align: center;
  padding: 40px 0 20px;
}

.complete__box {
  display: inline-flex;
  flex-direction: column;
  gap: 12px;
  padding: 28px 32px;
  border-radius: 20px;
  background: #f3f6fb;
}

.loading {
  padding: 16px 0 8px;
  color: #5b6475;
}

@keyframes rise {
  from {
    transform: translateY(18px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .card {
    padding: 20px;
  }

  .card__header {
    flex-direction: column;
    align-items: flex-start;
  }

  .card__header h1 {
    font-size: 24px;
  }

  /* Trên mobile thì cho bung full chiều rộng */
  input,
  select,
  textarea,
  .description-box {
    width: 100% !important; 
    font-size: 16px;
  }

  select option {
    font-size: 16px;
  }

  .actions {
    flex-direction: column;
    align-items: stretch;
  }

  button {
    width: 100%;
  }
}
</style>