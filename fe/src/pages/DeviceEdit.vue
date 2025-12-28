<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const apiBase = '/api'
const route = useRoute()
const router = useRouter()

const deviceKey = ref(route.query.id || route.query.name || '')
const loading = ref(false)
const loadError = ref('')
const serverError = ref('')
const submitting = ref(false)
const step = ref('input') // input | confirm | complete

const form = reactive({
  id: '',
  name: '',
  description: '',
  avatarExisting: '',
  avatarFile: null,
})

const errors = reactive({
  id: '',
  name: '',
  description: '',
  avatarFile: '',
})

const avatarPreview = ref('')
const currentAvatarUrl = computed(() => {
  if (avatarPreview.value) return avatarPreview.value
  if (form.avatarExisting) return `${apiBase}/web/avatar/${form.avatarExisting}`
  return ''
})

const resetErrors = () => {
  errors.id = ''
  errors.name = ''
  errors.description = ''
  errors.avatarFile = ''
}

const validate = () => {
  resetErrors()
  let ok = true
  if (!form.name.trim()) {
    errors.name = 'Hãy nhập tên thiết bị'
    ok = false
  }
  if (!form.description.trim()) {
    errors.description = 'Hãy nhập mô tả chi tiết'
    ok = false
  } else if (form.description.trim().length > 1000) {
    errors.description = 'Không nhập quá 1000 ký tự'
    ok = false
  }
  if (!form.avatarFile && !form.avatarExisting) {
    errors.avatarFile = 'Hãy chọn avatar'
    ok = false
  }
  return ok
}

const onAvatarChange = (e) => {
  const file = e.target.files?.[0] || null
  form.avatarFile = file
}

watch(
  () => form.avatarFile,
  (file, prev) => {
    if (avatarPreview.value) {
      URL.revokeObjectURL(avatarPreview.value)
      avatarPreview.value = ''
    }
    if (file) avatarPreview.value = URL.createObjectURL(file)
    if (!file && prev) form.avatarFile = null
  }
)

onBeforeUnmount(() => {
  if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
})

const fetchDetail = async () => {
  resetErrors()
  loadError.value = ''
  serverError.value = ''
  step.value = 'input'

  if (!deviceKey.value.toString().trim()) {
    errors.id = 'Nhập ID hoặc tên thiết bị'
    return
  }

  loading.value = true
  try {
    const qs = new URLSearchParams()
    const key = deviceKey.value.toString().trim()
    if (/^\d+$/.test(key)) qs.set('id', key)
    else qs.set('name', key)
    const res = await fetch(`${apiBase}/devices/detail?${qs.toString()}`)
    const data = await res.json().catch(() => ({}))
    if (!res.ok) throw new Error(data.error || 'Không tải được dữ liệu')

    form.id = data.device.id
    form.name = data.device.name || ''
    form.description = data.device.description || ''
    form.avatarExisting = data.device.avatar || ''
    form.avatarFile = null
    avatarPreview.value = ''
  } catch (e) {
    loadError.value = e.message || 'Không tải được dữ liệu'
  } finally {
    loading.value = false
  }
}

const goConfirm = () => {
  serverError.value = ''
  if (validate()) {
    step.value = 'confirm'
  }
}

const goEdit = () => {
  step.value = 'input'
}

const goHome = () => router.push('/')

const submit = async () => {
  if (submitting.value) return
  if (!validate()) {
    step.value = 'input'
    return
  }
  submitting.value = true
  serverError.value = ''
  try {
    const fd = new FormData()
    fd.append('id', form.id)
    fd.append('name', form.name.trim())
    fd.append('description', form.description.trim())
    if (form.avatarFile) fd.append('avatar', form.avatarFile)

    const res = await fetch(`${apiBase}/devices/update`, {
      method: 'POST',
      body: fd,
    })
    const payload = await res.json().catch(() => ({}))
    if (!res.ok) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((k) => {
          if (errors[k] !== undefined) errors[k] = payload.fields[k]
        })
      }
      serverError.value = payload.error || 'Cập nhật thất bại'
      step.value = 'input'
      return
    }
    if (payload.avatar) form.avatarExisting = payload.avatar
    form.avatarFile = null
    avatarPreview.value = ''
    step.value = 'complete'
  } catch (e) {
    serverError.value = 'Không thể kết nối máy chủ'
    step.value = 'input'
  } finally {
    submitting.value = false
  }
}

if (deviceKey.value) {
  fetchDetail()
}
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Device edit</p>
          <h1>Chỉnh sửa thiết bị</h1>
        </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Input</span>
          <span v-else-if="step === 'confirm'">Confirm</span>
          <span v-else>Complete</span>
        </div>
      </header>

      <div class="id-row">
        <label for="deviceId">ID hoặc tên thiết bị</label>
        <div class="id-input-wrap">
          <input
            id="deviceId"
            v-model="deviceKey"
            type="text"
            placeholder="Nhập ID hoặc tên thiết bị"
          />
          <button type="button" class="ghost" @click="fetchDetail" :disabled="loading">
            {{ loading ? 'Đang tải...' : 'Tải dữ liệu' }}
          </button>
        </div>
        <p v-if="errors.id" class="error">{{ errors.id }}</p>
        <p v-if="loadError" class="error">{{ loadError }}</p>
      </div>

      <form v-if="step === 'input'" class="form" @submit.prevent="goConfirm">
        <div class="form-grid">
          <label for="name">Tên thiết bị</label>
          <div>
            <input id="name" v-model="form.name" type="text" maxlength="255" placeholder="Nhập tên thiết bị" />
            <p v-if="errors.name" class="error">{{ errors.name }}</p>
          </div>

          <label for="description">Mô tả chi tiết</label>
          <div>
            <textarea
              id="description"
              v-model="form.description"
              maxlength="1000"
              rows="6"
              placeholder="Nhập mô tả chi tiết"
            ></textarea>
            <p v-if="errors.description" class="error">{{ errors.description }}</p>
          </div>

          <label for="avatar">Avatar</label>
          <div class="file-row">
            <div class="avatar-box" v-if="currentAvatarUrl">
              <img :src="currentAvatarUrl" alt="Avatar preview" />
            </div>
            <input id="avatar" type="file" accept="image/*" @change="onAvatarChange" />
            <span class="file-hint">Browse</span>
            <p v-if="errors.avatarFile" class="error">{{ errors.avatarFile }}</p>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary" :disabled="!form.id">Xác nhận</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Tên thiết bị</span>
          <span class="value">{{ form.name }}</span>

          <span class="label">Mô tả chi tiết</span>
          <div class="value description-box">{{ form.description }}</div>

          <span class="label">Avatar</span>
          <div class="avatar-box">
            <img v-if="currentAvatarUrl" :src="currentAvatarUrl" alt="Avatar preview" />
            <span v-else class="avatar-placeholder">IMAGE</span>
          </div>
        </div>
        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Sửa lại</button>
          <button type="button" class="primary" :disabled="submitting" @click="submit">
            {{ submitting ? 'Đang cập nhật...' : 'Đăng ký' }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Bạn đã chỉnh sửa thành công thiết bị.</h2>
          <p><a href="#" @click.prevent="goHome">Trở về trang chủ</a></p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 48px 20px 80px;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  background: radial-gradient(circle at top, #e1efff 0%, #f5f0e6 40%) no-repeat,
    linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
}
.card {
  width: min(900px, 100%);
  background: #fff;
  border: 1px solid #d6dce6;
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
.id-row {
  margin-bottom: 20px;
}
.id-input-wrap {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-top: 6px;
}
.form-grid {
  display: grid;
  grid-template-columns: 160px 1fr;
  gap: 16px 20px;
}
label,
.label {
  font-weight: 600;
  color: #5b6475;
  align-self: center;
}
input,
textarea {
  width: 100%;
  border: 1px solid #cdd7e5;
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
.file-row {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 12px;
  align-items: center;
}
input[type='file'] {
  padding: 8px;
  background: #f3f6fb;
}
.file-hint {
  padding: 10px 14px;
  border-radius: 10px;
  background: #2e6db4;
  color: white;
  font-size: 14px;
  text-align: center;
}
.actions {
  margin-top: 24px;
  display: flex;
  gap: 12px;
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
}
.avatar-box {
  width: 120px;
  height: 120px;
  border-radius: 16px;
  border: 1px dashed #cdd7e5;
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
  color: #2e6db4;
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
@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .card {
    padding: 24px;
  }
  .file-row {
    grid-template-columns: 1fr;
  }
}
</style>
