<template>
  <div class="login-page">
    <div class="login-card">
      <h2>Đăng nhập hệ thống</h2>
      <form @submit.prevent="onSubmit">
        <div class="form-group">
          <label for="login_id">Người dùng</label>
          <input id="login_id" v-model="form.login_id" type="text" autocomplete="username" />
        </div>
        <span v-if="errors.login_id" class="error-msg">{{ errors.login_id }}</span>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" v-model="form.password" type="password" autocomplete="current-password" />
        </div>
        <span v-if="errors.password" class="error-msg">{{ errors.password }}</span>

        <div class="link-group">
          <RouterLink to="/register">Đăng ký</RouterLink> | <RouterLink to="/reset-password-request">Quên password</RouterLink>
        </div>

        <div class="captcha-container">
          <div id="recaptcha"></div>
        </div>

        <span v-if="errors.login" class="main-error">{{ errors.login }}</span>

        <div class="btn-container">
          <button type="submit" class="btn-submit" :disabled="submitting">Đăng nhập</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({ login_id: '', password: '' })
const errors = reactive({ login_id: '', password: '', login: '' })
const submitting = ref(false)
const recaptchaSiteKey = ref("")
let recaptchaWidgetId = null

function renderRecaptcha() {
  if (window.grecaptcha && window.grecaptcha.render && recaptchaSiteKey.value && document.getElementById('recaptcha')) {
    if (recaptchaWidgetId === null) {
      try {
        recaptchaWidgetId = window.grecaptcha.render('recaptcha', {
          sitekey: recaptchaSiteKey.value
        })
      } catch (e) {
        console.error(e)
      }
    }
  } else {
    setTimeout(renderRecaptcha, 300)
  }
}

onMounted(async () => {
  try {
    const res = await fetch('/api/get_config.php')
    if (res.ok) {
      const config = await res.json()
      if (config.status === 'success' && config.site_key) {
/*************  ✨ Windsurf Command ⭐  *************/
/**
 * Handles the form submission event.
 * Resets the form errors, sets the submitting state to true, gets the recaptcha response if available, constructs the form data, sends a POST request to the backend, and handles the response.
 * If the response status is success, redirects to the homepage.
 * If the response contains errors, assigns them to the errors object.
 * If the response contains a message, assigns it to the login error.
 */
/*******  99b0c17a-a571-4613-b426-1cf001a5221f  *******/        recaptchaSiteKey.value = config.site_key
        renderRecaptcha()
      }
    }
  } catch (error) {
    console.error(error)
  }
})

const onSubmit = async () => {
  errors.login_id = ''
  errors.password = ''
  errors.login = ''
  
  if (!form.login_id) {
    errors.login_id = 'Hãy nhập login id'
    return
  }
  if (!form.password) {
    errors.password = 'Hãy nhập password'
    return
  }

  submitting.value = true
  let recaptchaResponse = ''
  
  if (window.grecaptcha && recaptchaWidgetId !== null) {
    recaptchaResponse = window.grecaptcha.getResponse(recaptchaWidgetId)
  }

  const body = new FormData()
  body.append('login_id', form.login_id)
  body.append('password', form.password)
  body.append('g-recaptcha-response', recaptchaResponse)

  try {
    const res = await fetch('/api/login.php', {
      method: 'POST',
      body
    })
    const result = await res.json()
    
    if (result.status === 'success') {
      router.push('/')
    } else if (result.errors) {
      Object.assign(errors, result.errors)
      if (window.grecaptcha && recaptchaWidgetId !== null) {
        window.grecaptcha.reset(recaptchaWidgetId)
      }
    } else if (result.message) {
      errors.login = result.message
      if (window.grecaptcha && recaptchaWidgetId !== null) {
        window.grecaptcha.reset(recaptchaWidgetId)
      }
    }
  } catch (error) {
    errors.login = 'Có lỗi xảy ra khi kết nối server'
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f6f1e8 0%, #dbe4f2 100%);
}
.login-card {
  width: min(500px, 96vw);
  background: #fff;
  border-radius: 24px;
  box-shadow: 0 4px 24px #2e6db420;
  padding: 40px 32px;
}
.login-card h2 {
  text-align: center;
  color: #2e6db4;
  margin-bottom: 24px;
}
.form-group {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}
.form-group label {
  width: 110px;
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
}
.form-group input:focus {
  border-color: #4a7ebb;
}
.link-group {
  text-align: center;
  margin-bottom: 20px;
}
.link-group a {
  font-style: italic;
  text-decoration: underline;
  color: #000;
  font-size: 15px;
  margin: 0 5px;
}
.captcha-container {
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
  min-height: 78px; 
}
.btn-container {
  display: flex;
  justify-content: center;
}
.btn-submit {
  background-color: #547ebc;
  color: white;
  border: none;
  padding: 10px 40px;
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
.error-msg {
  color: red;
  font-size: 13px;
  margin-left: 110px;
  margin-top: -16px;
  margin-bottom: 10px;
  display: block;
}
.main-error {
  color: red;
  text-align: center;
  margin-bottom: 20px;
  font-weight: bold;
  display: block;
}
</style>