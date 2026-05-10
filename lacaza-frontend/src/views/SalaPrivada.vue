<script setup>
// Vista de una sala privada: el corazón de la app.
// Muestra los hábitos del día, permite marcarlos y ve el ranking en vivo.

import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  verSala,
  marcarHabito,
  obtenerRegistrosDeHoy,
  cerrarDia,
} from '../services/api'

// props: el router nos pasa el idSala desde la URL
const props = defineProps({
  idSala: {
    type: [String, Number],
    required: true,
  },
})

const router = useRouter()
const sala = ref(null)
const ranking = ref([])
const habitosMarcadosHoy = ref([])
const puntosHoy = ref(0)
const cargando = ref(true)
const usuario = ref(null)

// Iconos por temática
const iconosTematica = {
  deporte: '🏋', estudio: '📚', bienestar: '🧘', trabajo: '💼', custom: '✨',
}

onMounted(async () => {
  const usuarioGuardado = localStorage.getItem('usuarioCazador')
  if (usuarioGuardado) usuario.value = JSON.parse(usuarioGuardado)
  await cargarTodo()
})

async function cargarTodo() {
  cargando.value = true
  try {
    const [respSala, respHoy] = await Promise.all([
      verSala(props.idSala),
      obtenerRegistrosDeHoy(props.idSala),
    ])
    sala.value = respSala.data.sala
    ranking.value = respSala.data.ranking
    habitosMarcadosHoy.value = respHoy.data.habitosMarcados
    puntosHoy.value = respHoy.data.puntosHoy
  } catch (e) {
    console.error(e)
    if (e.response?.status === 403) {
      alert('No participas en esta sala')
      router.push('/')
    }
  } finally {
    cargando.value = false
  }
}

// computed: valor que se recalcula automáticamente cuando cambian sus dependencias
const miPosicion = computed(() => {
  if (!ranking.value.length || !usuario.value) return 1
  const idx = ranking.value.findIndex(j => j.idUsuario === usuario.value.idUsuario)
  return idx >= 0 ? idx + 1 : 1
})

const miRacha = computed(() => {
  if (!ranking.value.length || !usuario.value) return 0
  const yo = ranking.value.find(j => j.idUsuario === usuario.value.idUsuario)
  return yo?.pivot?.rachaActual || 0
})

const diasRestantes = computed(() => {
  if (!sala.value) return 0
  const hoy = new Date()
  const fin = new Date(sala.value.fechaFin)
  const diff = Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24))
  return Math.max(0, diff)
})

// Comprueba si un hábito ya está marcado
function estaMarcado(idHabito) {
  return habitosMarcadosHoy.value.includes(idHabito)
}

// Al hacer clic en un hábito, lo marcamos
async function alPulsarHabito(habito) {
  if (estaMarcado(habito.idHabito)) {
    // Ya está marcado, no hacemos nada (de momento)
    return
  }

  try {
    const respuesta = await marcarHabito(habito.idHabito)
    // Recargamos los registros y el ranking
    habitosMarcadosHoy.value.push(habito.idHabito)
    puntosHoy.value += parseFloat(habito.puntos)

    // Actualizamos el ranking con la respuesta del backend
    if (respuesta.data.ranking) {
      ranking.value = respuesta.data.ranking
    }

    // Animación de "diana" (lo dejamos sencillo: solo log)
    console.log('¡DIANA! +' + habito.puntos)
  } catch (e) {
    alert(e.response?.data?.mensaje || 'Error al marcar el hábito')
  }
}

async function alCerrarDia() {
  try {
    const respuesta = await cerrarDia(props.idSala)
    alert(`Día cerrado. Racha: ${respuesta.data.rachaActual} días.`)
    await cargarTodo()
  } catch (e) {
    alert(e.response?.data?.mensaje || 'Error')
  }
}

function copiarCodigo() {
  if (!sala.value) return
  navigator.clipboard.writeText(sala.value.codigoInvitacion)
  alert('Código copiado: ' + sala.value.codigoInvitacion)
}
</script>

<template>
  <div class="contenedor">
    <div v-if="cargando" class="cargando">Cargando sala...</div>

    <div v-else-if="sala">
      <!-- Cabecera de la sala -->
      <header class="panel cabecera-sala">
        <button class="boton-volver" @click="router.push('/')">← Mis salas</button>

        <div style="text-align:center; margin-top: 20px">
          <div class="texto-monoespaciado">
            {{ iconosTematica[sala.tematica] }} {{ sala.tematica.toUpperCase() }}
          </div>
          <h1 class="titulo-vaquero nombre-sala">{{ sala.nombre }}</h1>
        </div>

        <div class="estadisticas">
          <div class="estat">
            <div class="etiqueta">Cazadores</div>
            <div class="valor">{{ ranking.length }}</div>
          </div>
          <div class="estat">
            <div class="etiqueta">Días restantes</div>
            <div class="valor">{{ diasRestantes }}</div>
          </div>
          <div class="estat">
            <div class="etiqueta">Tu posición</div>
            <div class="valor rojo">#{{ miPosicion }}</div>
          </div>
          <div class="estat">
            <div class="etiqueta">Tu racha</div>
            <div class="valor">{{ miRacha }} 🔥</div>
          </div>
        </div>
      </header>

      <!-- Dos columnas: hábitos y ranking -->
      <div class="dos-columnas">

        <!-- Columna izquierda: hábitos del día -->
        <div class="panel">
          <h2 class="titulo-vaquero seccion-titulo">La caza de hoy</h2>

          <div class="resumen-hoy">
            <div>
              <div class="texto-monoespaciado">Cada hábito suma</div>
              <div class="puntos-hoy">+{{ puntosHoy }} pts hoy</div>
            </div>
          </div>

          <div class="lista-habitos">
            <div
              v-for="habito in sala.habitos"
              :key="habito.idHabito"
              class="habito"
              :class="{ marcado: estaMarcado(habito.idHabito) }"
              @click="alPulsarHabito(habito)"
            >
              <div class="icono-habito">{{ habito.icono }}</div>
              <div class="info-habito">
                <div class="nombre-habito">{{ habito.nombre }}</div>
                <div class="descripcion-habito">{{ habito.descripcion }}</div>
              </div>
              <div class="recompensa">+{{ habito.puntos }}</div>
            </div>
          </div>

          <div class="botones-sala">
            <button class="boton principal" @click="alCerrarDia">Cerrar el día</button>
            <button class="boton" @click="copiarCodigo">Copiar código</button>
          </div>
        </div>

        <!-- Columna derecha: ranking -->
        <div class="panel">
          <h2 class="titulo-vaquero seccion-titulo">Tablón de caza</h2>

          <div class="cabecera-wanted">
            <div class="palabra-wanted">WANTED</div>
            <div class="texto-monoespaciado">★ El podio del mes ★</div>
          </div>

          <div class="lista-ranking">
            <div
              v-for="(jugador, indice) in ranking"
              :key="jugador.idUsuario"
              class="fila-ranking"
              :class="{ yo: jugador.idUsuario === usuario?.idUsuario }"
            >
              <div class="posicion-ranking">{{ indice + 1 }}</div>
              <div class="info-ranking">
                <div class="nombre-jugador">
                  {{ jugador.nombre }} {{ jugador.apellidos }}
                  <span v-if="jugador.idUsuario === usuario?.idUsuario" class="tu">(TÚ)</span>
                </div>
                <div class="racha-jugador">
                  🔥 Racha de {{ jugador.pivot?.rachaActual || 0 }} días
                </div>
              </div>
              <div class="puntuacion-jugador">
                {{ jugador.pivot?.puntosAcumulados || 0 }}
                <span class="pts">PTS</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.cargando {
  text-align: center;
  padding: 60px;
  font-family: 'Special Elite', monospace;
  letter-spacing: 2px;
}

.cabecera-sala {
  position: relative;
  text-align: center;
}
.boton-volver {
  position: absolute;
  top: 14px;
  left: 14px;
  background: transparent;
  border: none;
  font-family: 'Special Elite', monospace;
  font-size: 11px;
  letter-spacing: 1.5px;
  cursor: pointer;
  color: var(--color-tinta);
  text-transform: uppercase;
}
.boton-volver:hover { color: var(--color-sangre); }
.nombre-sala {
  font-size: 28px;
  color: var(--color-sangre);
  margin-top: 4px;
}
.estadisticas {
  margin-top: 16px;
  padding-top: 12px;
  border-top: 1.5px dashed var(--color-tinta);
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
  gap: 12px;
}
.estat .etiqueta {
  font-family: 'Special Elite', monospace;
  font-size: 9px;
  letter-spacing: 1.5px;
  color: var(--color-tinta-suave);
  text-transform: uppercase;
}
.estat .valor {
  font-family: 'Rye', serif;
  font-size: 20px;
  color: var(--color-tinta);
}
.estat .valor.rojo { color: var(--color-sangre); }

.dos-columnas {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
@media (max-width: 920px) {
  .dos-columnas { grid-template-columns: 1fr; }
}

.seccion-titulo {
  font-size: 20px;
  padding-bottom: 10px;
  margin-bottom: 16px;
  border-bottom: 2px solid var(--color-tinta);
}

.resumen-hoy {
  margin-bottom: 16px;
}
.puntos-hoy {
  font-family: 'Rye', serif;
  font-size: 22px;
  color: var(--color-tinta);
}

.lista-habitos {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.habito {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border: 1.5px solid var(--color-tinta);
  background: rgba(255, 253, 245, 0.5);
  cursor: pointer;
  transition: all 0.15s ease;
}
.habito:hover {
  transform: translateX(4px);
  background: rgba(227, 169, 48, 0.15);
}
.habito.marcado {
  background: var(--color-tinta);
  color: var(--color-papel);
}
.habito.marcado .nombre-habito {
  text-decoration: line-through;
  opacity: 0.7;
}
.habito.marcado .icono-habito {
  background: var(--color-oro-brillo);
}
.icono-habito {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--color-papel-oscuro);
  border: 2px solid var(--color-tinta);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.info-habito { flex: 1; }
.nombre-habito {
  font-weight: 600;
  font-size: 16px;
}
.descripcion-habito {
  font-family: 'Special Elite', monospace;
  font-size: 10px;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  opacity: 0.7;
}
.recompensa {
  font-family: 'Rye', serif;
  font-size: 16px;
  color: var(--color-sangre);
}
.habito.marcado .recompensa { color: var(--color-oro-brillo); }

.botones-sala {
  margin-top: 16px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.cabecera-wanted {
  text-align: center;
  padding: 10px 0;
  border-bottom: 2px solid var(--color-tinta);
  margin-bottom: 14px;
}
.palabra-wanted {
  font-family: 'Rye', serif;
  font-size: 26px;
  letter-spacing: 6px;
  color: var(--color-sangre);
}

.lista-ranking {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.fila-ranking {
  display: grid;
  grid-template-columns: 40px 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  border: 1.5px solid var(--color-tinta);
  background: rgba(255, 253, 245, 0.5);
}
.fila-ranking.yo {
  border: 2.5px solid var(--color-sangre);
  background: rgba(227, 169, 48, 0.16);
  box-shadow: 3px 3px 0 var(--color-sangre);
}
.posicion-ranking {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-papel-oscuro);
  border: 2px solid var(--color-tinta);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Rye', serif;
  font-size: 18px;
}
.fila-ranking:nth-child(1) .posicion-ranking { background: var(--color-oro-brillo); }
.fila-ranking:nth-child(2) .posicion-ranking { background: #c4c4ba; }
.fila-ranking:nth-child(3) .posicion-ranking { background: #c89a6a; }

.nombre-jugador {
  font-weight: 700;
  font-size: 15px;
}
.tu { color: var(--color-sangre); }
.racha-jugador {
  font-family: 'Special Elite', monospace;
  font-size: 10px;
  letter-spacing: 1.5px;
  color: var(--color-tinta-suave);
  text-transform: uppercase;
  margin-top: 2px;
}
.puntuacion-jugador {
  font-family: 'Rye', serif;
  font-size: 18px;
  color: var(--color-sangre);
  text-align: right;
}
.puntuacion-jugador .pts {
  font-family: 'Special Elite', monospace;
  font-size: 9px;
  letter-spacing: 2px;
  color: var(--color-tinta-suave);
  display: block;
}
</style>
