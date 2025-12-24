<template>
  <div class="register-page">
    <div class="register-card">
      <h2>Đăng ký tài khoản</h2>
      <form @submit.prevent="onSubmit">
        <div class="form-group">
          <label for="login_id">Login ID</label>
          <input id="login_id" v-model="form.login_id" type="text" />
        </div>
        <span v-if="errors.login_id" class="error-msg">{{ errors.login_id }}</span>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" v-model="form.password" type="password" />
        </div>
        <span v-if="errors.password" class="error-msg">{{ errors.password }}</span>

        <div class="form-group">
          <label for="confirm_password">Nhập lại Pass</label>
          <input id="confirm_password" v-model="form.confirm_password" type="password" />
        </div>
        <span v-if="errors.confirm_password" class="error-msg">{{ errors.confirm_password }}</span>

        <span v-if="errors.system" class="system-error">{{ errors.system }}</span>

        <div class="btn-container">
          <button type="submit" class="btn-submit" :disabled="submitting">Đăng ký</button>
        </div>
      </form>
      <RouterLink to="/login" class="back-link">← Quay lại đăng nhập</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({ login_id: '', password: '', confirm_password: '' })
const errors = reactive({ login_id: '', password: '', confirm_password: '', system: '' })
const submitting = ref(false)

const onSubmit = async () => {
  errors.login_id = ''
  errors.password = ''
  errors.confirm_password = ''
  errors.system = ''
  submitting.value = true
  const body = new FormData()
  body.append('login_id', form.login_id)
  body.append('password', form.password)
  body.append('confirm_password', form.confirm_password)
  const res = await fetch('/api/register.php', {
    method: 'POST',
    body
  })
  const result = await res.json()
  submitting.value = false
  if (result.status === 'success') {
    router.push('/login')
  } else if (result.errors) {
    Object.assign(errors, result.errors)
  } else if (result.message) {
    errors.system = result.message
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f6f1e8 0%, #dbe4f2 100%);
}
.register-card {
  width: min(450px, 96vw);
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 4px 24px #2e6db420;
  padding: 40px 32px;
}
.register-card h2 {
  text-align: center;
  color: #4a7ebb;
  margin-top: 0;
  margin-bottom: 30px;
  text-transform: uppercase;
  font-size: 20px;
}
.form-group {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}
.form-group label {
  width: 140px;
  font-size: 16px;
  color: #000;
}
.form-group input {
  flex: 1;
  padding: 8px;
  border: 1px solid #7f9db9;
  background-color: #e8f0fe;
  font-size: 14px;
  outline: none;
  border-radius: 6px;
  height: 35px;
}
.form-group input:focus {
  border-color: #4a7ebb;
}
.btn-container {
  display: flex;
  justify-content: center;
  margin-top: 10px;
}
.btn-submit {
  background-color: #547ebc;
  color: white;
  border: none;
  padding: 10px 35px;
  font-size: 16px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-submit:disabled {
  background-color: #b3c7e6;
  cursor: not-allowed;
}
.btn-submit:hover:not(:disabled) {
  background-color: #416aa6;
}
.back-link {
  display: block;
  text-align: center;
  margin-top: 20px;
  font-style: italic;
  text-decoration: underline;
  color: #000;
  font-size: 14px;
}
.error-msg {
  color: red;
  font-size: 13px;
  margin-left: 140px;
  margin-top: -16px;
  margin-bottom: 10px;
  display: block;
}
.system-error {
  color: red;
  text-align: center;
  margin-bottom: 20px;
}
</style>
