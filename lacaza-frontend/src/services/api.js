// Servicio de API: aquí está toda la comunicación con el backend Laravel.
// Usamos axios, una librería que facilita las peticiones HTTP.

import axios from 'axios'

// Creamos una instancia de axios configurada con la URL de nuestro backend
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // donde corre Laravel
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Interceptor: antes de cada petición, añadimos el token si lo tenemos guardado
// (es lo que demuestra al backend que estamos logueados)
api.interceptors.request.use((configuracion) => {
  const token = localStorage.getItem('tokenCazador')
  if (token) {
    configuracion.headers.Authorization = `Bearer ${token}`
  }
  return configuracion
})

// Interceptor de respuesta: si el backend nos devuelve "no autorizado" (401),
// borramos el token y mandamos al login
api.interceptors.response.use(
  (respuesta) => respuesta,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('tokenCazador')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// ─────────── FUNCIONES PARA HABLAR CON EL BACKEND ───────────

// AUTENTICACIÓN
export const registrarUsuario = (datos) => api.post('/registro', datos)
export const iniciarSesion = (datos) => api.post('/login', datos)
export const cerrarSesion = () => api.post('/logout')
export const obtenerUsuarioActual = () => api.get('/yo')

// SALAS
export const listarMisSalas = () => api.get('/salas')
export const verSala = (idSala) => api.get(`/salas/${idSala}`)
export const crearSala = (datos) => api.post('/salas', datos)
export const unirseASala = (codigo) => api.post('/salas/unirse', { codigo })

// REGISTRO DE HÁBITOS
export const marcarHabito = (idHabito) => api.post('/registros', { idHabito })
export const obtenerRegistrosDeHoy = (idSala) => api.get(`/salas/${idSala}/registros-hoy`)
export const cerrarDia = (idSala) => api.post(`/salas/${idSala}/cerrar-dia`)

// LOGROS
export const listarLogros = () => api.get('/logros')

export default api
