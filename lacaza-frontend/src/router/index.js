// Configuración del router: define las rutas de la aplicación.
// Vue Router se encarga de mostrar la vista correcta según la URL del navegador.

import { createRouter, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Registro from '../views/Registro.vue'
import MisSalas from '../views/MisSalas.vue'
import CrearSala from '../views/CrearSala.vue'
import SalaPrivada from '../views/SalaPrivada.vue'
import Logros from '../views/Logros.vue'

// Lista de rutas: cada una asocia una URL con un componente
const rutas = [
  { path: '/login',        name: 'login',        component: Login },
  { path: '/registro',     name: 'registro',     component: Registro },
  { path: '/',             name: 'misSalas',     component: MisSalas,    meta: { requiereLogin: true } },
  { path: '/crear-sala',   name: 'crearSala',    component: CrearSala,   meta: { requiereLogin: true } },
  { path: '/sala/:idSala', name: 'salaPrivada',  component: SalaPrivada, meta: { requiereLogin: true }, props: true },
  { path: '/logros',       name: 'logros',       component: Logros,      meta: { requiereLogin: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes: rutas,
})

// Guard de navegación: antes de cambiar de vista, comprobamos si el usuario tiene token
// Si la ruta requiere login y no hay token, lo mandamos al login
router.beforeEach((destino, origen, siguiente) => {
  const tieneToken = !!localStorage.getItem('tokenCazador')

  if (destino.meta.requiereLogin && !tieneToken) {
    siguiente('/login')
  } else if ((destino.name === 'login' || destino.name === 'registro') && tieneToken) {
    // Si ya estamos logueados, no tiene sentido ver el login
    siguiente('/')
  } else {
    siguiente()
  }
})

export default router
