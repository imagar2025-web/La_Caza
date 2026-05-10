<script setup>
// Vista de inicio de sesión.
// Pide email y contraseña, los manda al backend y guarda el token.

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { iniciarSesion } from '../services/api'

const router = useRouter()

// Variables reactivas: cuando cambian, la vista se actualiza sola
const email = ref('')
const contrasena = ref('')
const error = ref('')
const cargando = ref(false)

// Función que se ejecuta al pulsar "Entrar"
async function manejarLogin() {
  error.value = ''
  cargando.value = true

  try {
    // Llamamos al backend
    const respuesta = await iniciarSesion({
      email: email.value,
      contrasena: contrasena.value,
    })

    // Si todo bien, guardamos el token y los datos del usuario en localStorage
    localStorage.setItem('tokenCazador', respuesta.data.token)
    localStorage.setItem('usuarioCazador', JSON.stringify(respuesta.data.usuario))

    // Y vamos a la pantalla principal
    router.push('/')
  } catch (e) {
    // Si hay error, lo mostramos
    error.value = e.response?.data?.mensaje || 'Error al iniciar sesión'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="pantalla-login">
    <div class="caja-login panel">
      <h1 class="titulo-vaquero">LA CAZA</h1>
      <p class="texto-monoespaciado">★ Inicia sesión, cazador ★</p>

      <form @submit.prevent="manejarLogin">
        <div class="campo">
          <label for="email">Email</label>
          <input
            id="email"
            type="email"
            v-model="email"
            required
            placeholder="tu@email.com"
          />
        </div>

        <div class="campo">
          <label for="contrasena">Contraseña</label>
          <input
            id="contrasena"
            type="password"
            v-model="contrasena"
            required
            placeholder="••••••••"
          />
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="boton principal" :disabled="cargando">
          {{ cargando ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <p class="enlace-registro">
        ¿No tienes cuenta?
        <router-link to="/registro">Regístrate aquí</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.pantalla-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.caja-login {
  width: 100%;
  max-width: 420px;
  text-align: center;
}
.caja-login h1 {
  font-size: 38px;
  color: var(--color-sangre);
  margin-bottom: 6px;
}
.campo {
  margin: 16px 0;
  text-align: left;
}
.error {
  color: var(--color-sangre);
  font-weight: 600;
  margin: 12px 0;
  font-size: 14px;
}
.boton.principal {
  width: 100%;
  margin-top: 10px;
  padding: 14px;
  font-size: 14px;
}
.enlace-registro {
  margin-top: 20px;
  font-size: 14px;
  color: var(--color-tinta-suave);
}
.enlace-registro a {
  color: var(--color-sangre);
  font-weight: 700;
  text-decoration: none;
}
.enlace-registro a:hover { text-decoration: underline; }
</style>
