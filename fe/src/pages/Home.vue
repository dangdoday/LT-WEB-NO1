<template>
  <div class="home">
    <div class="card">
      <h1>Trang chu</h1>
      <p>Chon chuc nang de bat dau.</p>
      <a class="link" href="/teachers/register">Dang ky giao vien</a>
    </div>
  </div>
</template>

<style scoped>
.home {
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

.card h1 {
  margin: 0 0 12px;
}

.link {
  display: inline-block;
  margin-top: 12px;
  padding: 10px 16px;
  border-radius: 12px;
  background: #2e6db4;
  color: #ffffff;
  text-decoration: none;
  font-weight: 600;
}
</style>
