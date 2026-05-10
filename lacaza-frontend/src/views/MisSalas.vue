<script setup>
// Vista principal: muestra todas las salas en las que participa el cazador.

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { listarMisSalas, unirseASala, cerrarSesion } from '../services/api'
import TarjetaSala from '../components/TarjetaSala.vue'

const router = useRouter()
const salas = ref([])
const cargando = ref(true)
const mostrarFormularioCodigo = ref(false)
const codigoInvitacion = ref('')
const usuario = ref(null)

// onMounted: se ejecuta cuando la vista se carga por primera vez
onMounted(async () => {
  // Recuperamos el usuario que tenemos guardado en localStorage
  const usuarioGuardado = localStorage.getItem('usuarioCazador')
  if (usuarioGuardado) {
    usuario.value = JSON.parse(usuarioGuardado)
  }
  await cargarSalas()
})

async function cargarSalas() {
  cargando.value = true
  try {
    const respuesta = await listarMisSalas()
    salas.value = respuesta.data
  } catch (e) {
    console.error('Error al cargar las salas', e)
  } finally {
    cargando.value = false
  }
}

// Calcula la posición del usuario en una sala según los puntos
function obtenerPosicion(sala) {
  if (!sala.jugadores || !usuario.value) return 1
  const ordenados = [...sala.jugadores].sort(
    (a, b) => (b.pivot?.puntosAcumulados || 0) - (a.pivot?.puntosAcumulados || 0)
  )
  const idx = ordenados.findIndex(j => j.idUsuario === usuario.value.idUsuario)
  return idx >= 0 ? idx + 1 : 1
}

function abrirSala(idSala) {
  router.push(`/sala/${idSala}`)
}

function irACrear() {
  router.push('/crear-sala')
}

function irALogros() {
  router.push('/logros')
}

async function enviarCodigo() {
  if (!codigoInvitacion.value.trim()) return
  try {
    await unirseASala(codigoInvitacion.value.trim().toUpperCase())
    codigoInvitacion.value = ''
    mostrarFormularioCodigo.value = false
    await cargarSalas()
  } catch (e) {
    alert(e.response?.data?.mensaje || 'No se pudo unir a la sala')
  }
}

async function manejarLogout() {
  try {
    await cerrarSesion()
  } catch (e) { /* ignoramos errores aquí */ }
  localStorage.removeItem('tokenCazador')
  localStorage.removeItem('usuarioCazador')
  router.push('/login')
}
</script>

<template>
  <div class="contenedor">
    <!-- Cabecera -->
    <header class="cabecera panel">
      <div class="marca">
        <div class="emblema">🦊</div>
        <div>
          <div class="titulo-vaquero" style="font-size:22px">LA CAZA</div>
          <div class="texto-monoespaciado" style="font-size:9px">Hunter Future S.L.</div>
        </div>
      </div>

      <div class="acciones-cabecera">
        <button class="boton" @click="irALogros">🏆 Logros</button>
        <div v-if="usuario" class="info-usuario">
          <span class="avatar-usuario">{{ usuario.avatar || '🤠' }}</span>
          <span class="nombre-usuario">{{ usuario.nombre }}</span>
        </div>
        <button class="boton" @click="manejarLogout">Salir</button>
      </div>
    </header>

    <!-- Hero -->
    <div class="seccion-titulo">
      <h1 class="titulo-vaquero">TUS SALAS</h1>
      <p class="texto-monoespaciado">★ Compite · Supera · Celebra ★</p>
    </div>

    <!-- Botones de acción -->
    <div class="botones-accion">
      <button class="boton principal" @click="irACrear">
        + Crear nueva sala
      </button>
      <button class="boton" @click="mostrarFormularioCodigo = !mostrarFormularioCodigo">
        ⚔ Unirse con código
      </button>
    </div>

    <!-- Formulario de código (se muestra al pulsar el botón) -->
    <div v-if="mostrarFormularioCodigo" class="panel formulario-codigo">
      <label>Código de invitación</label>
      <input
        type="text"
        v-model="codigoInvitacion"
        placeholder="XXXX-XXXX"
        @keyup.enter="enviarCodigo"
      />
      <button class="boton principal" @click="enviarCodigo">Unirme</button>
    </div>

    <!-- Listado de salas -->
    <div v-if="cargando" class="cargando">Cargando tus salas...</div>

    <div v-else-if="salas.length === 0" class="vacio">
      <div style="font-size:48px">🤠</div>
      <p>Aún no tienes salas.</p>
      <p>Funda una o únete con un código para empezar la cacería.</p>
    </div>

    <div v-else class="rejilla-salas">
      <TarjetaSala
        v-for="sala in salas"
        :key="sala.idSala"
        :sala="sala"
        :posicion="obtenerPosicion(sala)"
        @abrir="abrirSala"
      />
    </div>
  </div>
</template>

<style scoped>
.cabecera {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  flex-wrap: wrap;
  gap: 14px;
}
.marca {
  display: flex;
  align-items: center;
  gap: 12px;
}
.emblema {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-oro-brillo);
  border: 2.5px solid var(--color-tinta);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
}
.acciones-cabecera {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.info-usuario {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--color-tinta);
  color: var(--color-papel);
  padding: 8px 14px;
  border: 2px solid var(--color-tinta);
  font-family: 'Special Elite', monospace;
  font-size: 12px;
}
.avatar-usuario { font-size: 16px; }
.nombre-usuario { letter-spacing: 1px; }

.seccion-titulo {
  text-align: center;
  padding: 20px 0;
}
.seccion-titulo h1 {
  font-size: 36px;
  color: var(--color-sangre);
  letter-spacing: 3px;
}

.botones-accion {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 20px;
}
@media (max-width: 560px) {
  .botones-accion { grid-template-columns: 1fr; }
}

.formulario-codigo input {
  margin: 10px 0;
}

.cargando, .vacio {
  text-align: center;
  padding: 40px;
  font-family: 'Special Elite', monospace;
  letter-spacing: 2px;
  color: var(--color-tinta-suave);
}

.rejilla-salas {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}
</style>
