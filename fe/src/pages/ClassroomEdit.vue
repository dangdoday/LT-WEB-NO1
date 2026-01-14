<script setup>
import { reactive, ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { BUILDINGS } from "@/constants/options.js";

const route = useRoute();
const router = useRouter();
const classroomId = route.params.id;

const step = ref("input");
const submitting = ref(false);
const loading = ref(true);
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
    errors.name = "Vui lòng nhập tên phòng.";
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

  // Not strictly required to change avatar on edit
  // if (!form.avatarFile && !avatarPreview.value) { ... }

  return ok;
};

// Fetch data
onMounted(async () => {
  try {
    const res = await fetch(`http://localhost:8000/classroom.php?id=${classroomId}`);
    if (!res.ok) throw new Error("Could not fetch classroom");
    const data = await res.json();
    
    form.name = data.name;
    form.description = data.description;
    form.building = data.building;
    if (data.avatar) {
      avatarPreview.value = `http://localhost:8000/${data.avatar}`;
    }
    loading.value = false;
  } catch (err) {
    console.error(err);
    alert("Không tìm thấy phòng học hoặc lỗi kết nối");
    router.push("/classrooms/search");
  }
});

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
  (file) => {
    if (file) {
      // If user picks a new file, show it
      if (avatarPreview.value && avatarPreview.value.startsWith("blob:")) {
         URL.revokeObjectURL(avatarPreview.value);
      }
      avatarPreview.value = URL.createObjectURL(file);
      avatarName.value = file.name;
    }
  }
);

onBeforeUnmount(() => {
  if (avatarPreview.value && avatarPreview.value.startsWith("blob:")) {
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
    if (form.avatarFile) {
        formData.append("avatar", form.avatarFile);
    }

    // Use POST with ID parameter to trigger UPDATE logic
    const res = await fetch(`http://localhost:8000/classroom.php?id=${classroomId}`, {
      method: "POST",
      body: formData,
    });

    const payload = await res.json().catch(() => ({}));

    if (!res.ok || payload.error) {
       if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) errors[key] = payload.fields[key];
        });
      }
      serverError.value = payload.error || "Cập nhật thất bại.";
      submitting.value = false;
      step.value = "input";
      return;
    }

    step.value = "complete";
  } catch (err) {
    console.error(err);
    serverError.value = "Không thể kết nối đến server.";
    step.value = "input";
  } finally {
    submitting.value = false;
  }
};
</script>

<template>
  <div class="page">
    <div class="card" v-if="!loading">
      <header class="card__header">
        <div>
          <p class="eyebrow">Phòng học</p>
          <h1>Cập nhật: {{ form.name }}</h1>
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
              placeholder="Nhập tên phòng học"
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
              Chọn ảnh khác
            </button>
            <span v-if="avatarName" class="file-name">{{ avatarName }}</span>
            <div v-else-if="avatarPreview" class="current-avatar-hint">
                <img :src="avatarPreview" alt="Current" class="mini-preview" />
                <span>(Ảnh hiện tại)</span>
            </div>
            <p v-if="errors.avatarFile" class="error">
              {{ errors.avatarFile }}
            </p>
          </div>

          <label for="description">Mô tả</label>
          <div>
            <textarea
              id="description"
              v-model="form.description"
              rows="6"
              placeholder="Nhập mô tả chi tiết"
            ></textarea>
            <p v-if="errors.description" class="error">
              {{ errors.description }}
            </p>
          </div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goHome">Hủy</button>
          <button type="submit" class="primary">Tiếp theo</button>
          
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
            <span v-else class="avatar-placeholder">KHÔNG CÓ ẢNH</span>
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
            {{ submitting ? "Đang xử lý..." : "Cập nhật" }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Cập nhật thành công</h2>
          <p>Thông tin phòng học đã được lưu lại.</p>
          <button type="button" class="primary" @click="goHome">
            Quay lại danh sách
          </button>
        </div>
      </div>
    </div>
    <div v-else class="loading">Đang tải...</div>
  </div>
</template>

<style scoped>
:root{
  --paper: #f5f0e6;
  --ink: #1d2330;
  --accent: #2e6db4;
  --line: #cdd7e5;
  --muted: #5b6475;
  --shadow: 0 24px 60px rgba(30,35,55,0.12);
}

*, *::before, *::after { box-sizing: border-box; }

.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
}

.card {
  width: min(880px, calc(100% - 40px));
  background: #fffdf8;
  border: 1px solid var(--line);
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: var(--shadow);
  margin: 0 20px;
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
  grid-template-columns: 180px 1fr;
  gap: 16px 20px;
  align-items: start;
}

label, .label {
  font-weight: 600;
  color: var(--muted);
  display: flex;
  align-items: center;
  justify-content: flex-end;
  text-align: right;
  padding-right: 12px;
}

input, select, textarea {
  width: 100%;
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
  color: var(--ink);
  display: block;
}

.confirm .label { text-align: left; justify-content: flex-start; }

textarea { resize: vertical; min-height: 140px; }

.file-row {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 44px;
}
.file-name {
  font-size: 14px;
  color: var(--muted);
  max-width: 40ch;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

input[type="file"] { display: none; }

.ghost {
  background: transparent;
  border: 1px solid var(--line);
  padding: 8px 12px;
  border-radius: 10px;
  cursor: pointer;
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
.avatar-placeholder { color: var(--muted); font-weight: 600; }

.value { align-self: center; }
.description-box { white-space: pre-wrap; }

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
.primary:disabled { opacity: 0.6; cursor: default; }

.error {
  color: #c44;
  margin-top: 6px;
  font-size: 13px;
}
.actions .error { width: 100%; text-align: center; margin-top: 8px; }

.complete__box { text-align: center; }

.mini-preview {
    width: 32px;
    height: 32px;
    object-fit: cover;
    border-radius: 4px;
}
.current-avatar-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted);
}

@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  label, .label { justify-content: flex-start; text-align: left; padding-right: 0; margin-bottom: 6px; }
  .card { padding: 20px; }
  .file-name { max-width: 60%; }
}
</style>
