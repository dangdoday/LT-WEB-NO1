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
    errors.device_id = "Hay chon thiet bi.";
    ok = false;
  }
  if (!form.teacher_id) {
    errors.teacher_id = "Hay chon giao vien.";
    ok = false;
  }
  if (!form.classroom_id) {
    errors.classroom_id = "Hay chon lop hoc.";
    ok = false;
  }
  if (!form.start_transaction_plan) {
    errors.start_transaction_plan = "Hay chon thoi gian bat dau.";
    ok = false;
  }
  if (!form.end_transaction_plan) {
    errors.end_transaction_plan = "Hay chon thoi gian ket thuc.";
    ok = false;
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
      serverError.value = payload.error || "Tao phieu muon that bai.";
      step.value = "input";
      return;
    }

    step.value = "complete";
  } catch (error) {
    serverError.value = "Khong the ket noi toi may chu.";
    step.value = "input";
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  try {
    const response = await fetch(`${apiBase}/transaction_form.php`);
    const payload = await response.json();
    if (!response.ok) {
      throw new Error(payload.error || "Load failed");
    }
    options.devices = payload.devices || [];
    options.teachers = payload.teachers || [];
    options.classrooms = payload.classrooms || [];
  } catch (error) {
    loadError.value = "Khong tai duoc du lieu. Vui long thu lai.";
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
          <h1>Muon thiet bi</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Input</span>
          <span v-else-if="step === 'confirm'">Confirm</span>
          <span v-else>Complete</span>
        </div>
      </header>

      <div v-if="loading" class="loading">Dang tai du lieu...</div>
      <div v-else-if="loadError" class="error">{{ loadError }}</div>

      <form
        v-else-if="step === 'input'"
        class="form"
        @submit.prevent="goConfirm"
      >
        <div class="form-grid">
          <label for="teacher">Giao vien</label>
          <div>
            <select id="teacher" v-model="form.teacher_id">
              <option disabled value="">Chon giao vien</option>
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

          <label for="device">Thiet bi</label>
          <div>
            <select id="device" v-model="form.device_id">
              <option disabled value="">Chon thiet bi</option>
              <option
                v-for="item in options.devices"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </option>
            </select>
            <p v-if="errors.device_id" class="error">{{ errors.device_id }}</p>
          </div>

          <label for="classroom">Lop hoc</label>
          <div>
            <select id="classroom" v-model="form.classroom_id">
              <option disabled value="">Chon lop hoc</option>
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

          <label for="start">Bat dau</label>
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

          <label for="end">Ket thuc</label>
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

          <label for="comment">Ghi chu</label>
          <textarea
            id="comment"
            v-model="form.comment"
            rows="4"
            placeholder="Mo ta them (neu co)"
          ></textarea>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Xac nhan</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Giao vien</span>
          <span class="value">{{ selectedTeacher?.name || "-" }}</span>

          <span class="label">Thiet bi</span>
          <span class="value">{{ selectedDevice?.name || "-" }}</span>

          <span class="label">Lop hoc</span>
          <span class="value">{{ selectedClassroom?.name || "-" }}</span>

          <span class="label">Bat dau</span>
          <span class="value">{{ form.start_transaction_plan }}</span>

          <span class="label">Ket thuc</span>
          <span class="value">{{ form.end_transaction_plan }}</span>

          <span class="label">Ghi chu</span>
          <div class="value description-box">{{ form.comment || "-" }}</div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Sua lai</button>
          <button
            type="button"
            class="primary"
            :disabled="submitting"
            @click="submit"
          >
            {{ submitting ? "Dang gui..." : "Dang ky muon" }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Dang ky muon thanh cong</h2>
          <p>Yeu cau muon thiet bi da duoc ghi nhan.</p>
          <button type="button" class="primary" @click="goHome">
            Tro ve trang chu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap");

:global(:root) {
  --paper: #f5f0e6;
  --ink: #1d2330;
  --accent: #2e6db4;
  --accent-2: #e0a42c;
  --line: #cdd7e5;
  --muted: #5b6475;
  --shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

:global(body) {
  margin: 0;
  font-family: "Manrope", system-ui, sans-serif;
  background: radial-gradient(circle at top, #f0e3cf 0%, #f5f0e6 45%) no-repeat,
    linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
  color: var(--ink);
}

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
  border: 1px solid var(--line);
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: var(--shadow);
  animation: rise 0.6s ease;
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
  color: var(--muted);
  margin: 0;
}

.step-pill {
  padding: 8px 16px;
  border-radius: 999px;
  background: rgba(46, 109, 180, 0.12);
  color: var(--accent);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.1em;
}

.form-grid {
  display: grid;
  grid-template-columns: 160px 1fr;
  gap: 16px 20px;
}

label,
.label {
  font-weight: 600;
  color: var(--muted);
  align-self: center;
}

input,
select,
textarea {
  width: 100%;
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
}

textarea {
  resize: vertical;
  min-height: 120px;
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
  background: var(--accent);
  color: white;
  box-shadow: 0 10px 20px rgba(46, 109, 180, 0.2);
}

button.ghost {
  background: #eef2f9;
  color: var(--accent);
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
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 14px;
  background: #fff;
  min-height: 120px;
  white-space: pre-wrap;
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
  color: var(--muted);
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
    padding: 24px;
  }

  .card__header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
