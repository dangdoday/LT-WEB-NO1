export function useClassroom() {
  const apiBase = import.meta.env.VITE_API_BASE || 'http://localhost:8000'

  async function createClassroom(formData) {
    const res = await fetch(`${apiBase}/classrooms`, {
      method: 'POST',
      body: formData,
    })
    const payload = await res.json().catch(() => ({}))
    return { ok: res.ok, status: res.status, payload }
  }

  return { createClassroom }
}
