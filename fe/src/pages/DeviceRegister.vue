<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute() // Dùng để lấy ID từ URL

const step = ref('input')
const submitting = ref(false)
const serverError = ref('')
const avatarPreview = ref('')

// Kiểm tra xem có đang ở chế độ Sửa không (có ID trên URL)
const isEditMode = computed(() => !!route.query.id)

const form = reactive({
  id: '',
  name: '',
  description: '',
  avatarFile: null,
  currentAvatar: '' // Lưu tên ảnh cũ để hiển thị lại nếu hủy chọn
})

const errors = reactive({
  name: '',
  description: '',
  avatarFile: '',
})

// --- LOGIC MỚI: Load dữ liệu cũ khi vào trang Sửa ---
onMounted(async () => {
  if (isEditMode.value) {
    try {
      // Gọi API lấy thông tin thiết bị (File này bạn đã tạo ở bước trước)
      const res = await fetch(`/api/get_device.php?id=${route.query.id}`)
      const data = await res.json()

      if (data.status === 'success') {
        const d = data.data
        form.id = d.id
        form.name = d.name
        form.description = d.description
        form.currentAvatar = d.avatar // Tên file ảnh trong DB

        // Hiển thị ảnh cũ (API get_device.php cần trả về image_url hoặc bạn tự nối chuỗi)
        // Giả sử đường dẫn ảnh là /api/web/avatar/
        avatarPreview.value = d.image_url || (d.avatar ? `/api/web/avatar/${d.avatar}` : '')
      } else {
        alert('Không tìm thấy thiết bị')
        router.push('/devices/search')
      }
    } catch (e) {
      console.error(e)
      serverError.value = 'Lỗi tải dữ liệu thiết bị.'
    }
  }
})

const resetErrors = () => {
  errors.name = ''
  errors.description = ''
  errors.avatarFile = ''
}

const validate = () => {
  resetErrors()
  let ok = true
  const name = form.name.trim()
  const description = form.description.trim()

  if (!name) {
    errors.name = 'Hay nhap ten thiet bi.'
    ok = false
  }

  if (!description) {
    errors.description = 'Hay nhap mo ta chi tiet.'
    ok = false
  } else if (description.length > 1000) {
    errors.description = 'Khong nhap qua 1000 ky tu'
    ok = false
  }

  // Logic validate ảnh:
  // - Nếu là Thêm mới: Bắt buộc chọn.
  // - Nếu là Sửa: Không bắt buộc (giữ ảnh cũ).
  if (!isEditMode.value && !form.avatarFile) {
    errors.avatarFile = 'Hay chon avatar'
    ok = false
  }

  return ok
}

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null
  form.avatarFile = file
}

watch(
    () => form.avatarFile,
    (file) => {
      // 1. Nếu chọn file mới -> Hiển thị file đó
      if (file) {
        if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
          URL.revokeObjectURL(avatarPreview.value)
        }
        avatarPreview.value = URL.createObjectURL(file)
      }
      // 2. Nếu hủy chọn file (file = null) VÀ đang sửa -> Hiện lại ảnh cũ
      else if (!file && isEditMode.value && form.currentAvatar) {
        avatarPreview.value = `/api/web/avatar/${form.currentAvatar}`
      }
      // 3. Xóa trắng
      else {
        avatarPreview.value = ''
      }
    }
)

onBeforeUnmount(() => {
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
})

const goConfirm = () => {
  serverError.value = ''
  if (validate()) {
    step.value = 'confirm'
  }
}

const goEdit = () => {
  step.value = 'input'
}

const goHome = () => {
  router.push('/')
}

const submit = async () => {
  if (submitting.value) return
  submitting.value = true
  serverError.value = ''

  try {
    const apiBase = import.meta.env.VITE_API_BASE || 'http://localhost:8000'
    const formData = new FormData()

    // Gửi ID nếu là Update
    if (isEditMode.value) {
      formData.append('id', form.id)
    }

    formData.append('name', form.name.trim())
    formData.append('description', form.description.trim())

    // Chỉ gửi file nếu có chọn
    if (form.avatarFile) {
      formData.append('avatar', form.avatarFile)
    }

    const response = await fetch(`${apiBase}/device_register.php`, {
      method: 'POST',
      body: formData,
    })

    const payload = await response.json().catch(() => ({}))

    if (!response.ok || payload.error) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) {
            errors[key] = payload.fields[key]
          }
        })
      }
      serverError.value = payload.error || (isEditMode.value ? 'Cap nhat that bai.' : 'Dang ky that bai.')
      submitting.value = false
      step.value = 'input'
      return
    }

    step.value = 'complete'
  } catch (error) {
    serverError.value = 'Khong the ket noi toi may chu.'
    step.value = 'input'
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">{{ isEditMode ? 'Update Device' : 'Device registration' }}</p>
          <h1>{{ isEditMode ? 'Cap nhat thiet bi' : 'Dang ky thiet bi' }}</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Input</span>
          <span v-else-if="step === 'confirm'">Confirm</span>
          <span v-else>Complete</span>
        </div>
      </header>

      <!-- STEP 1: INPUT -->
      <form v-if="step === 'input'" class="form" @submit.prevent="goConfirm">
        <div class="form-grid">
          <label for="name">Ten thiet bi</label>
          <div>
            <input id="name" v-model="form.name" type="text" maxlength="255" placeholder="Nhap ten thiet bi" />
            <p v-if="errors.name" class="error">{{ errors.name }}</p>
          </div>

          <label for="description">Mo ta them</label>
          <div>
            <textarea
                id="description"
                v-model="form.description"
                maxlength="1000"
                placeholder="Nhap mo ta chi tiet"
                rows="6"
            ></textarea>
            <p v-if="errors.description" class="error">{{ errors.description }}</p>
          </div>

          <label for="avatar">Avatar</label>
          <div>
            <!-- Preview ảnh ở bước nhập liệu -->
            <div v-if="avatarPreview" class="avatar-box mb-3">
              <img :src="avatarPreview" alt="Avatar preview" />
            </div>

            <div class="file-row">
              <!-- Input file ẩn đi -->
              <input id="avatar" type="file" accept="image/*" @change="onAvatarChange" />
              <!-- Label đóng vai trò nút bấm -->
              <label for="avatar" class="file-hint">Browse</label>
            </div>

            <p v-if="isEditMode" style="font-size:12px; color:#666; margin-top:5px;">
              (Bo trong neu khong muon doi anh)
            </p>
            <p v-if="errors.avatarFile" class="error">{{ errors.avatarFile }}</p>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Xac nhan</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <!-- STEP 2: CONFIRM -->
      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Ten thiet bi</span>
          <span class="value">{{ form.name }}</span>

          <span class="label">Mo ta chi tiet</span>
          <div class="value description-box">
            {{ form.description }}
          </div>

          <span class="label">Avatar</span>
          <div class="avatar-box">
            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar preview" />
            <span v-else class="avatar-placeholder">IMAGE</span>
          </div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Sua lai</button>
          <button type="button" class="primary" :disabled="submitting" @click="submit">
            {{ submitting ? 'Dang xu ly...' : (isEditMode ? 'Cap nhat' : 'Dang ky') }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <!-- STEP 3: COMPLETE -->
      <div v-else class="complete">
        <div class="complete__box">
          <h2>{{ isEditMode ? 'Cap nhat thanh cong' : 'Dang ky thanh cong' }}</h2>
          <p>{{ isEditMode ? 'Thong tin thiet bi da duoc cap nhat.' : 'Ban da dang ky thiet bi thanh cong.' }}</p>
          <button type="button" class="primary" @click="goHome">Tro ve trang chu</button>
          <button type="button" class="ghost" @click="router.push('/transactions/search')">Danh sach thiet bi</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

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
  font-family: 'Space Grotesk', system-ui, sans-serif;
  background: radial-gradient(circle at top, #e1efff 0%, #f5f0e6 40%) no-repeat,
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
  width: min(880px, 100%);
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
  align-self: flex-start; /* Căn label lên trên */
  padding-top: 10px;
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
  min-height: 140px;
}

/* SỬA LỖI NÚT BROWSE Ở ĐÂY */
.file-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

input[type='file'] {
  display: none; /* Ẩn input file mặc định */
}

/* Biến thẻ label thành nút bấm */
.file-hint {
  padding: 10px 14px;
  border-radius: 10px;
  background: var(--accent);
  color: white;
  font-size: 14px;
  text-align: center;
  cursor: pointer;
  display: inline-block;
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

/* Avatar Box */
.avatar-box {
  width: 140px;
  height: 140px;
  border-radius: 16px;
  border: 1px dashed var(--line);
  background: #f1f4fb;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.avatar-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  font-weight: 700;
  color: var(--accent);
}

.mb-3 { margin-bottom: 12px; }

.description-box {
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 14px;
  background: #fff;
  min-height: 140px;
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