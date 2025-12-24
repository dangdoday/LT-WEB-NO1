<template>
  <div class="page">
    <div class="card">
      <header class="card__header">
        <h1>Reset password</h1>
        <p class="eyebrow">Chon hanh dong</p>
      </header>
      <div class="links">
        <RouterLink class="link" to="/password/request">Gui yeu cau reset password</RouterLink>
        <RouterLink class="link" to="/password/reset">Danh sach reset password</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  background: linear-gradient(135deg, #f6f1e8 0%, #dbe4f2 100%);
}

.card {
  background: #ffffff;
  border-radius: 20px;
  padding: 28px 32px;
  border: 1px solid #d6dce6;
  width: min(560px, 100%);
  text-align: center;
}

.card__header {
  margin-bottom: 12px;
}

.card__header h1 {
  margin: 0 0 8px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #5b6475;
  font-size: 12px;
  font-weight: 600;
}

.links {
  display: grid;
  gap: 12px;
  margin-top: 16px;
}

.link {
  display: inline-block;
  padding: 12px 16px;
  border-radius: 12px;
  background: #2e6db4;
  color: #ffffff;
  text-decoration: none;
  font-weight: 700;
}
</style>