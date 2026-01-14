<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const loadError = ref('')
const submitting = ref(false)
const serverError = ref('')
const avatarPreview = ref('')
const fileNameDisplay = ref('Không có tệp nào được chọn')

const extractFileName = (str) => {
  if (!str) return ''
  return str.split('/').pop()
}

const form = reactive({
  id: '',
  name: '',
  description: '',
  avatarFile: null,
  currentAvatar: '',
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

const fetchDetail = async () => {
  const id = route.query.id
  if (!id) {
    loadError.value = 'Thiếu id thiết bị.'
    loading.value = false
    return
  }
  try {
    const res = await fetch(`/api/device_detail.php?id=${id}`)
    const data = await res.json()
    if (!res.ok || data.error) {
      throw new Error(data.error || 'Không tải được thiết bị')
    }
    form.id = data.data.id
    form.name = data.data.name || ''
    form.description = data.data.description || ''
    form.currentAvatar = data.data.avatar || ''
    avatarPreview.value = data.data.avatar_url || (data.data.avatar ? `/api/web/avatar/${data.data.avatar}` : '')
    fileNameDisplay.value = extractFileName(data.data.avatar || '') || 'Không có tệp nào được chọn'
  } catch (e) {
    loadError.value = 'Không tải được thiết bị.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDetail)

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
  return ok
}

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null
  form.avatarFile = file
  fileNameDisplay.value = file ? file.name : (extractFileName(form.currentAvatar) || 'Không có tệp nào được chọn')
}

watch(
  () => form.avatarFile,
  (file, previous) => {
    if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
      URL.revokeObjectURL(avatarPreview.value)
    }
    if (file) {
      avatarPreview.value = URL.createObjectURL(file)
    } else if (!file && previous && form.currentAvatar) {
      avatarPreview.value = form.currentAvatar ? `/api/web/avatar/${form.currentAvatar}` : ''
    }
  }
)

onBeforeUnmount(() => {
  if (avatarPreview.value && avatarPreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(avatarPreview.value)
  }
})

const goBack = () => {
  router.push({ name: 'device-search' })
}

const submit = async () => {
  if (submitting.value) return
  serverError.value = ''
  if (!validate()) return
  submitting.value = true

  try {
    const apiBase = import.meta.env.VITE_API_BASE || 'http://localhost:8000'
    const formData = new FormData()
    formData.append('id', form.id)
    formData.append('name', form.name.trim())
    formData.append('description', form.description.trim())
    if (form.avatarFile) {
      formData.append('avatar', form.avatarFile)
    }

    const res = await fetch(`${apiBase}/device_update.php`, {
      method: 'POST',
      body: formData,
    })
    const payload = await res.json().catch(() => ({}))
    if (!res.ok || payload.error) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) errors[key] = payload.fields[key]
        })
      }
      serverError.value = payload.error || 'Cập nhật thất bại.'
      return
    }
    router.push({ name: 'device-search' })
  } catch (e) {
    serverError.value = 'Không thể kết nối tới máy chủ.'
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
          <h1>Cập nhật thiết bị</h1>
        </div>
      </header>

      <div v-if="loading" class="loading">Đang tải...</div>
      <div v-else-if="loadError" class="error">{{ loadError }}</div>

      <div v-else class="confirm">
        <div class="form-grid">
          <span class="label">Tên thiết bị</span>
          <input v-model="form.name" type="text" maxlength="255" />
          <p v-if="errors.name" class="error">{{ errors.name }}</p>

          <span class="label">Mô tả chi tiết</span>
          <textarea v-model="form.description" rows="5" maxlength="1000"></textarea>
          <p v-if="errors.description" class="error">{{ errors.description }}</p>

          <span class="label">Avatar</span>
          <div class="avatar-inline">
            <div v-if="avatarPreview" class="avatar-box avatar-preview">
              <img :src="avatarPreview" alt="Avatar preview" />
            </div>
            <div class="file-row">
              <input id="avatar" type="file" accept="image/*" @change="onAvatarChange" />
              <div class="file-overlay">
                <span class="file-button">Chọn tệp</span>
                <span class="file-text">{{ fileNameDisplay }}</span>
              </div>
              <p v-if="errors.avatarFile" class="error">{{ errors.avatarFile }}</p>
            </div>
          </div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goBack">Quay lại</button>
          <button type="button" class="primary" :disabled="submitting" @click="submit">
            {{ submitting ? 'Đang cập nhật...' : 'Cập nhật' }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

:global(body) {
  margin: 0;
  font-family: 'Space Grotesk', system-ui, sans-serif;
  background: radial-gradient(circle at top, #e1efff 0%, #f5f0e6 40%) no-repeat,
    linear-gradient(135deg, #f5f0e6 0%, #dbe4f2 100%);
  color: #1d2330;
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
  border: 1px solid #cdd7e5;
  border-radius: 24px;
  padding: 28px 32px 36px;
  box-shadow: 0 24px 60px rgba(30, 35, 55, 0.12);
}

.card__header h1 {
  margin: 8px 0 0;
  font-size: 30px;
}

.eyebrow {
  text-transform: uppercase;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: 0.14em;
  color: #5b6475;
  margin: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 160px 1fr;
  gap: 16px 22px;
  max-width: 850px;
}

.label {
  font-weight: 600;
  color: #5b6475;
  align-self: center;
  padding-top: 0;
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
  min-height: 120px;
}

.avatar-box {
  width: 140px;
  height: 140px;
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
  position: relative;
}

.file-row input[type='file'] {
  position: relative;
  z-index: 2;
  opacity: 0;
  width: 100%;
  height: 29px;
  padding: 10px 14px;
  background: transparent;
  border: none;
  cursor: pointer;
}

.file-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 10px;
  background: #f3f6fb;
  border: 1px solid #cdd7e5;
  border-radius: 12px;
  pointer-events: none;
  box-sizing: border-box;
}

.file-button {
  padding: 6px 10px;
  border-radius: 4px;
  background: #ffffff;
  color: #1d2330;
  font-weight: 400;
  font-size: 14px;
  border: 1px solid #cdd7e5;
}

.file-text {
  color: #1d2330;
  font-size: 14px;
  word-break: break-all;
  flex: 1;
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

.loading {
  color: #5b6475;
}
</style>
