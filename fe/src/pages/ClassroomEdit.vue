<script setup>
import { reactive, ref, watch, onBeforeUnmount, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useClassroom } from "../composables/useClassroom";
import { BUILDINGS } from "@/constants/options.js";

const router = useRouter();
const route = useRoute();
const { getClassroom, updateClassroom } = useClassroom();

const step = ref("input");
const submitting = ref(false);
const serverError = ref("");
const avatarPreview = ref("");
const form = reactive({
  name: "",
  description: "",
  building: "",
  avatarFile: null,
});

const errors = reactive({
  name: "",
  description: "",
  building: "",
  avatarFile: "",
});

const resetErrors = () => {
  errors.name = "";
  errors.description = "";
  errors.building = "";
  errors.avatarFile = "";
};

const validate = () => {
  resetErrors();
  let ok = true;
  const name = form.name.trim();
  const description = form.description.trim();

  if (!name) {
    errors.name = "Vui lòng nhập tên.";
    ok = false;
  } else if (name.length > 100) {
    errors.name = "Tên quá dài.";
    ok = false;
  }

  if (!description) {
    errors.description = "Vui lòng nhập mô tả.";
    ok = false;
  } else if (description.length > 1000) {
    errors.description = "Mô tả quá dài.";
    ok = false;
  }

  if (!form.building) {
    errors.building = "Vui lòng chọn tòa nhà.";
    ok = false;
  }

  // avatarFile is optional for edit

  return ok;
};

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null;
  form.avatarFile = file;
  avatarName.value = file ? file.name : "";
};

const avatarInput = ref(null);
const avatarName = ref("");

const triggerFileInput = () => {
  if (avatarInput.value) avatarInput.value.click();
};

watch(
  () => form.avatarFile,
  (file, previous) => {
    // Only revoke if it was a blob url we created
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value);
    }
    
    if (file) {
      avatarPreview.value = URL.createObjectURL(file);
      avatarName.value = file.name;
    } else {
        // If file removed, we might revert to original image? 
        // For simplicity, if file removed, we just clear preview unless we want to show original.
    }
  }
);

const existingAvatarUrl = ref("");

onMounted(async () => {
    const id = route.params.id;
    if(!id) {
        alert("Không tìm thấy ID");
        router.push("/");
        return;
    }

    const { ok, payload } = await getClassroom(id);
    if(ok) {
        form.name = payload.name;
        form.description = payload.description;
        form.building = payload.building;
        if(payload.avatar) {
             existingAvatarUrl.value = `http://localhost:8000/${payload.avatar}`;
             avatarPreview.value = existingAvatarUrl.value;
        }
    } else {
        alert("Không thể tải thông tin phòng học");
        router.push("/classrooms/search");
    }
});

onBeforeUnmount(() => {
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value);
  }
});

const goConfirm = () => {
  serverError.value = "";
  if (validate()) step.value = "confirm";
};

const goEdit = () => (step.value = "input");
const goHome = () => router.push("/classrooms/search");

const submit = async () => {
  if (submitting.value) return;
  submitting.value = true;
  serverError.value = "";

  try {
    const formData = new FormData();
    formData.append("name", form.name.trim());
    formData.append("description", form.description.trim());
    formData.append("building", form.building);
    if(form.avatarFile) {
        formData.append("avatar", form.avatarFile);
    }

    const { ok, payload } = await updateClassroom(route.params.id, formData);

    if (!ok) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) errors[key] = payload.fields[key];
        });
      }
      serverError.value = payload.error || "Cập nhật phòng học thất bại.";
      submitting.value = false;
      step.value = "input";
      return;
    }

    step.value = "complete";
  } catch (err) {
    serverError.value = "Không thể kết nối đến máy chủ.";
    step.value = "input";
  } finally {
    submitting.value = false;
  }
};
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">PHÒNG HỌC</p>
          <h1>Chỉnh sửa phòng học</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Nhập liệu</span>
          <span v-else-if="step === 'confirm'">Xác nhận</span>
          <span v-else>Hoàn tất</span>
        </div>
      </header>

      <form v-if="step === 'input'" class="form" @submit.prevent="goConfirm">
        <div class="form-grid">
          <label for="name">Tên phòng</label>
          <div>
            <input
              id="name"
              v-model="form.name"
              type="text"
              maxlength="255"
              placeholder="Nhập tên lớp"
            />
            <p v-if="errors.name" class="error">{{ errors.name }}</p>
          </div>

          <label for="building">Tòa nhà</label>
          <div>
            <select id="building" v-model="form.building">
              <option value="" disabled hidden>Chọn tòa nhà</option>

              <option v-for="b in BUILDINGS" :key="b" :value="b">
                {{ b }}
              </option>
            </select>
            <p v-if="errors.building" class="error">{{ errors.building }}</p>
          </div>

          <label for="avatar">Ảnh đại diện</label>
          <div class="file-row">
            <input
              ref="avatarInput"
              id="avatar"
              name="avatar"
              type="file"
              accept="image/*"
              @change="onAvatarChange"
              style="display: none"
            />

            <button type="button" class="ghost" @click="triggerFileInput">
              Chọn ảnh
            </button>
            <span v-if="avatarName" class="file-name">{{ avatarName }}</span>
            <p v-if="errors.avatarFile" class="error">
              {{ errors.avatarFile }}
            </p>
             <div v-if="!form.avatarFile && existingAvatarUrl" class="current-avatar-hint">
                (Giữ nguyên ảnh cũ nếu không chọn ảnh mới)
             </div>
          </div>

          <label for="description">Mô tả</label>
          <div>
            <textarea
              id="description"
              v-model="form.description"
              rows="6"
              placeholder="Nhập mô tả"
            ></textarea>
            <p v-if="errors.description" class="error">
              {{ errors.description }}
            </p>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Xác nhận</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Tên phòng</span>
          <span class="value">{{ form.name }}</span>

          <span class="label">Tòa nhà</span>
          <span class="value">{{ form.building }}</span>

          <span class="label">Ảnh đại diện</span>
          <div class="avatar-box">
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              alt="Avatar preview"
            />
            <span v-else class="avatar-placeholder">ẢNH</span>
          </div>

          <span class="label">Mô tả</span>
          <div class="value description-box">{{ form.description }}</div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Sửa lại</button>
          <button
            type="button"
            class="primary"
            :disabled="submitting"
            @click="submit"
          >
            {{ submitting ? "Đang lưu..." : "Cập nhật" }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2 class="success-title">Cập nhật thành công</h2>
          <p class="success-message">
            Bạn đã cập nhật thông tin phòng học. <br/>
            Truy cập <a href="#" @click.prevent="goHome">danh sách</a> để xem chi tiết.
          </p>
          <button class="primary full-width-btn" @click="goHome">Quay về trang chủ</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

:root {
  --paper: #f5f0e6;
  --ink: #1d2330;
  --accent: #2e6db4;
  --line: #cdd7e5;
  --muted: #5b6475;
  --shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

*, *::before, *::after {
  box-sizing: border-box;
}

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: transparent;
}

.card {
  width: min(880px, 100%);
  background: #fffdf8;
  border: 1px solid var(--line);
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: var(--shadow);
}
/* ... existing styles ... */


.complete__box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  gap: 20px;
  background: #f0f4f9;
  border-radius: 20px;
  width: 100%;
  max-width: 500px;
  margin: 40px auto;
  text-align: center;
  border: 1px solid #d0e1f3; /* Optional: subtle border to match style */
}

.success-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--ink);
  margin: 0;
}

.success-message {
  font-size: 15px;
  color: var(--muted);
  margin: 0;
  line-height: 1.5;
}

.success-message a {
  color: var(--accent);
  text-decoration: underline;
  cursor: pointer;
}

.full-width-btn {
  width: 100%;
  margin-top: 10px;
  font-size: 16px;
  font-weight: 600;
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
  font-size: 28px;
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
  background: rgba(46, 109, 180, 0.08);
  color: var(--accent);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
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
  align-self: flex-start; /* Align label to top of input for textareas */
  padding-top: 14px; /* Visual alignment with input text */
}

input,
select,
textarea,
.file-row {
  width: 100%;
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
  color: var(--ink);
}

textarea {
  resize: vertical;
  min-height: 140px;
}

.file-row {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 48px; /* Match height of other inputs roughly */
}

/* Remove default border/background from inputs inside file-row if any... 
   Actually the button and text are inside. */

.file-name {
    font-size: 0.9em;
    color: #555;
    flex: 1; /* Allow filename to take available space */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.current-avatar-hint {
    font-size: 12px;
    color: #888;
    font-style: italic;
    white-space: nowrap;
}

.avatar-box {
  width: 120px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7f7f7;
  border-radius: 8px;
  border: 1px dashed var(--line);
}
.avatar-box img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
  border-radius: 6px;
}
.avatar-placeholder {
  color: var(--muted);
  font-weight: 600;
}

.value {
  align-self: center;
}
.description-box {
  white-space: pre-wrap;
}

.actions {
  margin-top: 18px;
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
}
.primary {
  background: var(--accent);
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  cursor: pointer;
}
.ghost {
  background: transparent;
  border: 1px solid var(--line);
  padding: 8px 12px;
  border-radius: 10px;
  cursor: pointer;
  color: var(--ink);
}
.primary:disabled {
  opacity: 0.6;
  cursor: default;
}

.error {
  color: #c44;
  margin-top: 6px;
  font-size: 13px;
}
.actions .error {
  width: 100%;
  text-align: center;
  margin-top: 8px;
}



@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .card {
    padding: 20px;
  }
  label, .label {
      padding-top: 0;
  }
}
</style>
