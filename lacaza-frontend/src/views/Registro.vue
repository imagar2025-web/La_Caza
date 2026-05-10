<script setup>
// Vista de registro de un nuevo cazador.

import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { registrarUsuario } from '../services/api'

const router = useRouter()

const nombre = ref('')
const apellidos = ref('')
const email = ref('')
const contrasena = ref('')
const error = ref('')
const cargando = ref(false)

async function manejarRegistro() {
  error.value = ''
  cargando.value = true

  try {
    const respuesta = await registrarUsuario({
      nombre: nombre.value,
      apellidos: apellidos.value,
      email: email.value,
      contrasena: contrasena.value,
    })

    localStorage.setItem('tokenCazador', respuesta.data.token)
    localStorage.setItem('usuarioCazador', JSON.stringify(respuesta.data.usuario))
    router.push('/')
  } catch (e) {
    // Mostramos el primer error de validación que devuelva Laravel
    const erroresLaravel = e.response?.data?.errors
    if (erroresLaravel) {
      error.value = Object.values(erroresLaravel)[0][0]
    } else {
      error.value = e.response?.data?.mensaje || 'Error al registrarse'
    }
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="pantalla-registro">
    <div class="caja-registro panel">
      <h1 class="titulo-vaquero">CREAR CUENTA</h1>
      <p class="texto-monoespaciado">★ Únete a la caza ★</p>

      <form @submit.prevent="manejarRegistro">
        <div class="fila">
          <div class="campo">
            <label for="nombre">Nombre</label>
            <input id="nombre" type="text" v-model="nombre" required maxlength="50" />
          </div>
          <div class="campo">
            <label for="apellidos">Apellidos</label>
            <input id="apellidos" type="text" v-model="apellidos" required maxlength="100" />
          </div>
        </div>

        <div class="campo">
          <label for="email">Email</label>
          <input id="email" type="email" v-model="email" required />
        </div>

        <div class="campo">
          <label for="contrasena">Contraseña (mínimo 6 caracteres)</label>
          <input id="contrasena" type="password" v-model="contrasena" required minlength="6" />
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <button type="submit" class="boton principal" :disabled="cargando">
          {{ cargando ? 'Creando cuenta...' : 'Crear cuenta' }}
        </button>
      </form>

      <p class="enlace-login">
        ¿Ya tienes cuenta?
        <router-link to="/login">Inicia sesión</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.pantalla-registro {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.caja-registro {
  width: 100%;
  max-width: 480px;
  text-align: center;
}
.caja-registro h1 {
  font-size: 32px;
  color: var(--color-sangre);
  margin-bottom: 6px;
}
.fila {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.campo {
  margin: 14px 0;
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
.enlace-login {
  margin-top: 20px;
  font-size: 14px;
}
.enlace-login a {
  color: var(--color-sangre);
  font-weight: 700;
  text-decoration: none;
}
@media (max-width: 480px) {
  .fila { grid-template-columns: 1fr; }
}
</style>
