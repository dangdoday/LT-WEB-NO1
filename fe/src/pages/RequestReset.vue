<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const step = ref('input')
const submitting = ref(false)
const serverError = ref('')

const form = reactive({
  login_id: '',
})

const errors = reactive({
  login_id: '',
})

const resetErrors = () => {
  errors.login_id = ''
}

const validate = () => {
  resetErrors()
  const loginId = form.login_id.trim()
  let ok = true

  if (!loginId) {
    errors.login_id = 'Hãy nhập login id'
    ok = false
  } else if (loginId.length < 4) {
    errors.login_id = 'Hãy nhập login id tối thiểu 4 ký tự'
    ok = false
  }

  return ok
}

const submit = async () => {
  if (submitting.value) return
  submitting.value = true
  serverError.value = ''

  if (!validate()) {
    submitting.value = false
    return
  }

  try {
    const apiBase = import.meta.env.VITE_API_BASE || '/api'
    const response = await fetch(`${apiBase}/request_reset.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ login_id: form.login_id.trim() }),
    })
    const payload = await response.json().catch(() => ({}))

    if (!response.ok) {
      if (payload.fields && payload.fields.login_id) {
        errors.login_id = payload.fields.login_id
      }
      serverError.value = payload.error || 'Gửi yêu cầu thất bại.'
      submitting.value = false
      return
    }

    step.value = 'complete'
  } catch (error) {
    serverError.value = 'Không thể kết nối tới máy chủ.'
  } finally {
    submitting.value = false
  }
}

const goLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <div>
          <p class="eyebrow">Password reset</p>
          <h1>Gửi yêu cầu reset password</h1>
        </div>
      </header>

      <form v-if="step === 'input'" class="form" @submit.prevent="submit">
        <div class="form-grid">
          <label for="loginId">Người dùng</label>
          <div>
            <input
              id="loginId"
              v-model="form.login_id"
              type="text"
              maxlength="100"
              placeholder="Nhập login id"
            />
            <p v-if="errors.login_id" class="error">{{ errors.login_id }}</p>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="primary" :disabled="submitting">
            {{ submitting ? 'Đang gửi...' : 'Gửi yêu cầu reset password' }}
          </button>

          <button type="button" class="back" @click="goLogin">
            Quay về login
          </button>
        </div>

        <p v-if="serverError" class="error">{{ serverError }}</p>
      </form>

      <div v-else class="complete">
        <div class="complete__box">
          <h2>Gửi yêu cầu thành công</h2>
          <button type="button" class="primary" @click="goLogin">Quay về login</button>
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
  background: radial-gradient(circle at top, #f4e6ff 0%, #f5f0e6 40%) no-repeat,
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
  width: min(720px, 100%);
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
  gap: 16px 20px;
  align-items: center;
}

label {
  font-weight: 600;
  color: #5b6475;
  align-self: center;
}

input {
  width: 500px;        /* 👈 cố định chiều rộng input */
  max-width: 100%;     /* 👈 chống vỡ mobile */
  border: 1px solid #cdd7e5;
  background: #fff;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 15px;
  font-family: inherit;
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

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  margin: 6px 0 0;
  color: #b13535;
  font-size: 13px;
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

.reset-link {
  color: #2e6db4;
  font-weight: 600;
  text-decoration: underline;
}

@media (max-width: 720px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .card {
    padding: 24px;
  }
}
.actions {
  margin-top: 28px;
  display: flex;
  gap: 16px;
  align-items: center;
}

/* nút chính giữ nguyên */
button.primary {
  background: #2e6db4;
  color: white;
  box-shadow: 0 10px 20px rgba(46, 109, 180, 0.2);
}

/* nút back */
button.back {
  background: transparent;
  color: #2e6db4;
  border: 1px solid #cdd7e5;
}

/* hover nhẹ */
button.back:hover {
  background: #f3f6fb;
}

</style>
