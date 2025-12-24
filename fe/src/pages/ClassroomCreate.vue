<script setup>
import { reactive, ref, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { useClassroom } from '../composables/useClassroom'

const router = useRouter()
const { createClassroom } = useClassroom()

const step = ref('input')
const submitting = ref(false)
const serverError = ref('')
const avatarPreview = ref('')

const form = reactive({
  name: '',
  description: '',
  building: '',
  avatarFile: null,
})

const errors = reactive({
  name: '',
  description: '',
  building: '',
  avatarFile: '',
})

const resetErrors = () => {
  errors.name = ''
  errors.description = ''
  errors.building = ''
  errors.avatarFile = ''
}

const validate = () => {
  resetErrors()
  let ok = true
  const name = form.name.trim()
  const description = form.description.trim()

  if (!name) {
    errors.name = 'Please enter a name.'
    ok = false
  } else if (name.length > 100) {
    errors.name = 'Name is too long.'
    ok = false
  }

  if (!description) {
    errors.description = 'Please enter a description.'
    ok = false
  } else if (description.length > 1000) {
    errors.description = 'Description is too long.'
    ok = false
  }

  if (!form.building) {
    errors.building = 'Please enter the building.'
    ok = false
  }

  if (!form.avatarFile) {
    errors.avatarFile = 'Please select an avatar image.'
    ok = false
  }

  return ok
}

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null
  form.avatarFile = file
  avatarName.value = file ? file.name : ''
}

const avatarInput = ref(null)
const avatarName = ref('')

const triggerFileInput = () => {
  if (avatarInput.value) avatarInput.value.click()
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
      avatarName.value = file.name
    }
    if (!file && previous) {
      form.avatarFile = null
      avatarName.value = ''
    }
  }
)

onBeforeUnmount(() => {
  if (avatarPreview.value) URL.revokeObjectURL(avatarPreview.value)
})

const goConfirm = () => {
  serverError.value = ''
  if (validate()) step.value = 'confirm'
}

const goEdit = () => (step.value = 'input')
const goHome = () => router.push('/')

const submit = async () => {
  if (submitting.value) return
  submitting.value = true
  serverError.value = ''

  try {
    const formData = new FormData()
    formData.append('name', form.name.trim())
    formData.append('description', form.description.trim())
    formData.append('building', form.building)
    formData.append('avatar', form.avatarFile)


    const { ok, payload } = await createClassroom(formData)

    if (!ok) {
      if (payload.fields) {
        Object.keys(payload.fields).forEach((key) => {
          if (errors[key] !== undefined) errors[key] = payload.fields[key]
        })
      }
      serverError.value = payload.error || 'Create classroom failed.'
      submitting.value = false
      step.value = 'input'
      return
    }

    step.value = 'complete'
  } catch (err) {
    serverError.value = 'Cannot reach server.'
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
              <p class="eyebrow">Classroom</p>
              <h1>Create classroom</h1>
            </div>
        <div class="step-pill">
          <span v-if="step === 'input'">Input</span>
          <span v-else-if="step === 'confirm'">Confirm</span>
          <span v-else>Complete</span>
        </div>
      </header>

      <form v-if="step === 'input'" class="form" @submit.prevent="goConfirm">
        <div class="form-grid">
          <label for="name">Name</label>
          <div>
            <input id="name" v-model="form.name" type="text" maxlength="255" placeholder="Nhap ten lop" />
            <p v-if="errors.name" class="error">{{ errors.name }}</p>
          </div>

          <label for="building">Building</label>
          <div>
            <select id="building" v-model="form.building">
              <option value="" disabled hidden>Select building</option>
              <option value="D1">D1</option>
              <option value="D2">D2</option>
              <option value="D3">D3</option>
              <option value="D4">D4</option>
              <option value="D5">D5</option>
            </select>
            <p v-if="errors.building" class="error">{{ errors.building }}</p>
          </div>

          <label for="avatar">Avatar</label>
          <div class="file-row">
            <input
              ref="avatarInput"
              id="avatar"
              name="avatar"
              type="file"
              accept="image/*"
              @change="onAvatarChange"
              style="display: none;"
            />

            <button type="button" class="ghost" @click="triggerFileInput">Browse</button>
            <span v-if="avatarName" class="file-name">{{ avatarName }}</span>
            <p v-if="errors.avatarFile" class="error">{{ errors.avatarFile }}</p>
          </div>

          <label for="description">Description</label>
          <div>
            <textarea id="description" v-model="form.description" rows="6" placeholder="Nhap mo ta"></textarea>
            <p v-if="errors.description" class="error">{{ errors.description }}</p>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary">Confirm</button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </form>

      <div v-else-if="step === 'confirm'" class="confirm">
        <div class="form-grid">
          <span class="label">Name</span>
          <span class="value">{{ form.name }}</span>

          <span class="label">Building</span>
          <span class="value">{{ form.building }}</span>

            <span class="label">Avatar</span>
          <div class="avatar-box">
            <img v-if="avatarPreview" :src="avatarPreview" alt="Avatar preview" />
            <span v-else class="avatar-placeholder">IMAGE</span>
          </div>

          <span class="label">Description</span>
          <div class="value description-box">{{ form.description }}</div>
        </div>

        <div class="actions">
          <button type="button" class="ghost" @click="goEdit">Edit</button>
          <button type="button" class="primary" :disabled="submitting" @click="submit">
            {{ submitting ? 'Working...': 'Create classroom' }}
          </button>
          <p v-if="serverError" class="error">{{ serverError }}</p>
        </div>
      </div>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Classroom created</h2>
          <p>The classroom was created successfully.</p>
          <button type="button" class="primary" @click="goHome">Back to home</button>
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
  color: var(--ink);
}

textarea { resize: vertical; min-height: 140px }

.file-row { display:flex; align-items:center; gap:12px }
.file-hint { font-size: 13px; color: var(--muted) }

.avatar-box { width: 120px; height: 80px; display:flex; align-items:center; justify-content:center; background:#f7f7f7; border-radius:8px; border:1px dashed var(--line) }
.avatar-box img { max-width:100%; max-height:100%; object-fit:cover; border-radius:6px }
.avatar-placeholder { color: var(--muted); font-weight:600 }

.value { align-self: center }
.description-box { white-space: pre-wrap; }

.actions {
  margin-top: 18px;
  display: flex;
  gap: 12px;
  align-items: center;
  justify-content: center; /* center buttons horizontally */
  flex-wrap: wrap;
}
.primary { background: var(--accent); color: #fff; border: none; padding: 10px 16px; border-radius: 10px; cursor: pointer }
.ghost { background: transparent; border: 1px solid var(--line); padding: 8px 12px; border-radius: 10px; cursor: pointer }
.primary:disabled { opacity: 0.6; cursor: default }

.error { color: #c44; margin-top:6px; font-size:13px }

/* When errors are inside the actions row, make them span full width and center */
.actions .error { width: 100%; text-align: center; margin-top: 8px }

.complete__box { text-align:center }

@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
  .card { padding: 20px }
}
</style>
