<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const step = ref('input')
const submitting = ref(false)
const serverError = ref('')
const avatarPreview = ref('')

const form = reactive({
  name: '',
  description: '',
  avatarFile: null,
})

const errors = reactive({
  name: '',
  description: '',
  avatarFile: '',
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
    errors.name = 'Hãy nhập tên thiết bị.'
    ok = false
  }

  if (!description) {
    errors.description = 'Hãy nhập mô tả chi tiết.'
    ok = false
  } else if (description.length > 1000) {
    errors.description = 'Không nhập quá 1000 ký tự'
    ok = false
  }

  if (!form.avatarFile) {
    errors.avatarFile = 'Hãy chọn avatar'
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
  (file, previous) => {
    if (avatarPreview.value) {
      URL.revokeObjectURL(avatarPreview.value)
      avatarPreview.value = ''
    }
    if (file) {
      avatarPreview.value = URL.createObjectURL(file)
    }
    if (!file && previous) {
      form.avatarFile = null
    }
  }
)

onBeforeUnmount(() => {
  if (avatarPreview.value) {
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
    formData.append('name', form.name.trim())
    formData.append('description', form.description.trim())
    formData.append('avatar', form.avatarFile)

    const response = await fetch(`${apiBase}/device_register.php`, {
      method: 'POST',
      body: formData,
    })

    const payload = await response.json().catch(() => ({}))
    if (!response.ok) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) {
            errors[key] = payload.fields[key]
          }
        })
      }
      serverError.value = payload.error || 'Đăng ký thất bại.'
      submitting.value = false
      step.value = 'input'
      return
    }

    step.value = 'complete'
  } catch (error) {
    serverError.value = 'Không thể kết nối tới máy chủ.'
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
          <p class="eyebrow">THIẾT BỊ</p>
          <h1>Đăng ký thiết bị</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">NHẬP LIỆU</span>
          <span v-else-if="step === 'confirm'">XÁC NHẬN</span>
          <span v-else>HOÀN TẤT</span>
        </div>
      </header>

      <form v-show="step === 'input'" class="form" @submit.prevent="goConfirm">
        <div class="form-grid">
          <label for="name">Tên thiết bị</label>
          <div>
            <input id="name" v-model="form.name" type="text" maxlength="255" placeholder="Nhập tên thiết bị" />
            <p v-if="errors.name" class="error">{{ errors.name }}</p>
          </div>

          <label for="description">Mô tả thêm</label>
          <div>
            <textarea
              id="description"
              v-model="form.description"
              maxlength="1000"
              placeholder="Nhập mô tả chi tiết"
              rows="6"
            ></textarea>
            <p v-if="errors.description" class="error">{{ errors.description }}</p>
          </div>

          <label for="avatar">Avatar</label>
          <div class="avatar-inline">
            <div v-if="avatarPreview" class="avatar-box avatar-preview">
              <img :src="avatarPreview" alt="Avatar preview" />
            </div>
            <div class="file-row compact">
              <input id="avatar" type="file" accept="image/*" @change="onAvatarChange" />
              <p v-if="errors.avatarFile" class="error">{{ errors.avatarFile }}</p>
            </div>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Xác nhận</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-show="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Tên thiết bị</span>
          <span class="value">{{ form.name }}</span>

          <span class="label">Mô tả chi tiết</span>
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
          <button type="button" class="ghost" @click="goEdit">Sửa lại</button>
          <button type="button" class="primary" :disabled="submitting" @click="submit">
            {{ submitting ? 'Đăng ký...' : 'Đăng ký' }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-show="step === 'complete'" class="complete">
        <div class="complete__box">
          <h2>Đăng ký thiết bị thành công</h2>
          <p>Bạn đã đăng ký thiết bị thành công.</p>
          <button type="button" class="primary" @click="goHome">Trở về trang chủ</button>
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
  gap: 16px 22px;
  max-width: 850px;
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
  min-height: 140px;
}

.avatar-inline {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 10px;
  width: 100%;
  max-width: 850px;
}

.avatar-preview {
  margin-bottom: 4px;
}

.file-row {
  width: 100%;
}

.file-row input[type='file'] {
  width: 100%;
  height: 29px;
  padding: 10px 14px;
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
    max-width: 100%;
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
